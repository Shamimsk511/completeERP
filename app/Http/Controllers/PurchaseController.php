<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Godown;
use App\Models\ErpFeatureSetting;
use App\Models\Company;
use App\Models\Category;
use App\Models\Payee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\GodownStockService;
use App\Services\PayeeAccountService;
use App\Services\Accounting\AutoPostingService;

class PurchaseController extends Controller
{
    function __construct()
{
    $this->middleware('permission:purchase-list|purchase-create|purchase-edit|purchase-delete', ['only' => ['index', 'show', 'getProductDetails']]);
    $this->middleware('permission:purchase-create', ['only' => ['create', 'store', 'createProduct']]);
    $this->middleware('permission:purchase-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:purchase-delete', ['only' => ['destroy']]);
}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with('company')->latest()->get();
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::suppliers()->orderBy('name')->get();
        $products = Product::with('company', 'category')->get();
        $categories = Category::orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        return view('purchases.create', compact('companies', 'products', 'categories', 'godowns'));
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        // Enhanced validation with better error messages
        $rules = [
            'purchase_date' => 'required|date',
            'invoice_no' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'godown_id' => 'nullable|exists:godowns,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.purchase_price' => 'required|numeric|min:0.01',
            // Additional costs validation
            'labour_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'other_cost_description' => 'nullable|string|max:255',
            'cost_distribution_method' => 'nullable|in:per_quantity,per_value,equal',
            'update_product_prices' => 'nullable|boolean',
        ];

        if (!ErpFeatureSetting::isEnabled('godown_management')) {
            unset($rules['godown_id']);
        }

        $request->validate($rules, [
            'items.required' => 'At least one product item is required.',
            'items.*.product_id.required' => 'Product selection is required for all items.',
            'items.*.quantity.required' => 'Quantity is required for all items.',
            'items.*.purchase_price.required' => 'Purchase price is required for all items.',
        ]);

        try {
            DB::beginTransaction();

            // Filter out empty items
            $validItems = array_filter($request->items, function($item) {
                return !empty($item['product_id']) &&
                       !empty($item['quantity']) &&
                       !empty($item['purchase_price']);
            });

            if (empty($validItems)) {
                throw new Exception('No valid items found. Please add at least one product.');
            }

            // Calculate additional costs
            $labourCost = floatval($request->labour_cost ?? 0);
            $transportationCost = floatval($request->transportation_cost ?? 0);
            $otherCost = floatval($request->other_cost ?? 0);
            $totalAdditionalCost = $labourCost + $transportationCost + $otherCost;

            $godownId = null;
            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $godownId = GodownStockService::resolveGodownId($request->godown_id);
            }

            // Create purchase
            $purchase = Purchase::create([
                'purchase_date' => $request->purchase_date,
                'invoice_no' => $request->invoice_no,
                'company_id' => $request->company_id,
                'godown_id' => $godownId,
                'notes' => $request->notes,
                'total_amount' => 0,
                'labour_cost' => $labourCost,
                'transportation_cost' => $transportationCost,
                'other_cost' => $otherCost,
                'other_cost_description' => $request->other_cost_description,
                'cost_distribution_method' => $request->cost_distribution_method ?? 'per_value',
                'update_product_prices' => $request->boolean('update_product_prices'),
                'grand_total' => 0,
            ]);

            $totalAmount = 0;
            $totalQuantity = 0;

            // First pass: calculate totals for distribution
            foreach ($validItems as $item) {
                $totalAmount += floatval($item['quantity']) * floatval($item['purchase_price']);
                $totalQuantity += floatval($item['quantity']);
            }

            // Add purchase items with cost distribution
            foreach ($validItems as $item) {
                $quantity = floatval($item['quantity']);
                $purchasePrice = floatval($item['purchase_price']);
                $totalPrice = $quantity * $purchasePrice;

                // Calculate additional cost for this item
                $additionalCost = 0;
                $effectivePrice = $purchasePrice;

                if ($totalAdditionalCost > 0) {
                    $method = $request->cost_distribution_method ?? 'per_value';

                    switch ($method) {
                        case 'per_quantity':
                            $additionalCost = ($totalQuantity > 0) ? ($totalAdditionalCost / $totalQuantity) * $quantity : 0;
                            $effectivePrice = $purchasePrice + ($totalAdditionalCost / $totalQuantity);
                            break;
                        case 'equal':
                            $additionalCost = $totalAdditionalCost / count($validItems);
                            $effectivePrice = $purchasePrice + ($additionalCost / $quantity);
                            break;
                        case 'per_value':
                        default:
                            $proportion = ($totalAmount > 0) ? $totalPrice / $totalAmount : 0;
                            $additionalCost = $totalAdditionalCost * $proportion;
                            $effectivePrice = $purchasePrice + ($additionalCost / $quantity);
                            break;
                    }
                }

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'godown_id' => $godownId,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'total_price' => $totalPrice,
                    'additional_cost' => $additionalCost,
                    'effective_price' => $effectivePrice,
                ]);

                // Update product stock and purchase price
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->current_stock += $quantity;
                    // Update purchase price with effective price if option is selected
                    if ($request->boolean('update_product_prices') && $totalAdditionalCost > 0) {
                        $product->purchase_price = round($effectivePrice, 2);
                    } else {
                        $product->purchase_price = $purchasePrice;
                    }
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $resolvedGodownId = GodownStockService::resolveGodownId($godownId, $product);
                        if ($resolvedGodownId) {
                            GodownStockService::adjustStock($product->id, $resolvedGodownId, $quantity);
                        }
                    }
                }
            }

            // Update purchase totals
            $purchase->total_amount = $totalAmount;
            $purchase->grand_total = $totalAmount + $totalAdditionalCost;
            $purchase->save();

            $this->syncSupplierPayeeBalance($purchase->company, $purchase->grand_total);
            app(AutoPostingService::class)->postPurchase($purchase);

            DB::commit();

            // Handle "Create & New" functionality
            if ($request->has('create_and_new')) {
                return redirect()->route('purchases.create')->with('success', 'Purchase created successfully. You can create another one.');
            }

            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Purchase creation error: ' . $e->getMessage());
            return back()->with('error', 'Error creating purchase: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['company', 'items.product.category']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $purchase->load(['items.product']);
        $companies = Company::suppliers()->orderBy('name')->get();
        $products = Product::with('company', 'category')->get();
        $categories = Category::orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        return view('purchases.edit', compact('purchase', 'companies', 'products', 'categories', 'godowns'));
    }

    /**
 * Search products for AJAX request (for Select2 lazy loading)
 */
public function searchProducts(Request $request)
{
    $query = $request->get('q', '');
    $page = $request->get('page', 1);
    $perPage = 30;
    
    $products = Product::with('company', 'category')
        ->where('name', 'LIKE', "%{$query}%")
        ->orWhereHas('company', function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%");
        })
        ->paginate($perPage, ['*'], 'page', $page);
    
    $items = $products->map(function($product) {
        return [
            'id' => $product->id,
            'text' => $product->name . ' - ' . $product->company->name,
            'purchase_price' => $product->purchase_price,
            'box_pcs' => $product->category->box_pcs ?? 0,
            'pieces_feet' => $product->category->pieces_feet ?? 0,
        ];
    });
    
    return response()->json([
        'items' => $items,
        'total_count' => $products->total(),
        'page' => $page,
        'per_page' => $perPage
    ]);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $rules = [
            'purchase_date' => 'required|date',
            'invoice_no' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'godown_id' => 'nullable|exists:godowns,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.purchase_price' => 'required|numeric|min:0.01',
            // Additional costs validation
            'labour_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'other_cost_description' => 'nullable|string|max:255',
            'cost_distribution_method' => 'nullable|in:per_quantity,per_value,equal',
            'update_product_prices' => 'nullable|boolean',
        ];

        if (!ErpFeatureSetting::isEnabled('godown_management')) {
            unset($rules['godown_id']);
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Calculate additional costs
            $labourCost = floatval($request->labour_cost ?? 0);
            $transportationCost = floatval($request->transportation_cost ?? 0);
            $otherCost = floatval($request->other_cost ?? 0);
            $totalAdditionalCost = $labourCost + $transportationCost + $otherCost;

            $godownId = null;
            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $godownId = GodownStockService::resolveGodownId($request->godown_id);
            }

            // Update purchase details
            $purchase->update([
                'purchase_date' => $request->purchase_date,
                'invoice_no' => $request->invoice_no,
                'company_id' => $request->company_id,
                'godown_id' => $godownId,
                'notes' => $request->notes,
                'labour_cost' => $labourCost,
                'transportation_cost' => $transportationCost,
                'other_cost' => $otherCost,
                'other_cost_description' => $request->other_cost_description,
                'cost_distribution_method' => $request->cost_distribution_method ?? 'per_value',
                'update_product_prices' => $request->boolean('update_product_prices'),
            ]);

            $existingItems = PurchaseItem::withoutGlobalScopes()
                ->where('purchase_id', $purchase->id)
                ->get();

            // Revert previous stock updates
            foreach ($existingItems as $oldItem) {
                $product = Product::find($oldItem->product_id);
                $product->current_stock -= $oldItem->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $oldGodownId = $oldItem->godown_id ?? $purchase->godown_id;
                    $resolvedOldGodownId = GodownStockService::resolveGodownId($oldGodownId, $product);
                    if ($resolvedOldGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedOldGodownId, -$oldItem->quantity);
                    }
                }
            }

            // Delete old items
            PurchaseItem::withoutGlobalScopes()->where('purchase_id', $purchase->id)->delete();

            // Filter valid items
            $validItems = array_filter($request->items, function($item) {
                return !empty($item['product_id']) &&
                       !empty($item['quantity']) &&
                       !empty($item['purchase_price']);
            });

            // First pass: calculate totals for distribution
            $totalAmount = 0;
            $totalQuantity = 0;
            foreach ($validItems as $item) {
                $totalAmount += floatval($item['quantity']) * floatval($item['purchase_price']);
                $totalQuantity += floatval($item['quantity']);
            }

            // Add new items with cost distribution
            foreach ($validItems as $item) {
                $quantity = floatval($item['quantity']);
                $purchasePrice = floatval($item['purchase_price']);
                $totalPrice = $quantity * $purchasePrice;

                // Calculate additional cost for this item
                $additionalCost = 0;
                $effectivePrice = $purchasePrice;

                if ($totalAdditionalCost > 0) {
                    $method = $request->cost_distribution_method ?? 'per_value';

                    switch ($method) {
                        case 'per_quantity':
                            $additionalCost = ($totalQuantity > 0) ? ($totalAdditionalCost / $totalQuantity) * $quantity : 0;
                            $effectivePrice = $purchasePrice + ($totalAdditionalCost / $totalQuantity);
                            break;
                        case 'equal':
                            $additionalCost = $totalAdditionalCost / count($validItems);
                            $effectivePrice = $purchasePrice + ($additionalCost / $quantity);
                            break;
                        case 'per_value':
                        default:
                            $proportion = ($totalAmount > 0) ? $totalPrice / $totalAmount : 0;
                            $additionalCost = $totalAdditionalCost * $proportion;
                            $effectivePrice = $purchasePrice + ($additionalCost / $quantity);
                            break;
                    }
                }

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'godown_id' => $godownId,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'total_price' => $totalPrice,
                    'additional_cost' => $additionalCost,
                    'effective_price' => $effectivePrice,
                ]);

                // Update product stock and purchase price
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->current_stock += $quantity;
                    // Update purchase price with effective price if option is selected
                    if ($request->boolean('update_product_prices') && $totalAdditionalCost > 0) {
                        $product->purchase_price = round($effectivePrice, 2);
                    } else {
                        $product->purchase_price = $purchasePrice;
                    }
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $resolvedGodownId = GodownStockService::resolveGodownId($godownId, $product);
                        if ($resolvedGodownId) {
                            GodownStockService::adjustStock($product->id, $resolvedGodownId, $quantity);
                        }
                    }
                }
            }

            // Update totals
            $purchase->total_amount = $totalAmount;
            $purchase->grand_total = $totalAmount + $totalAdditionalCost;
            $purchase->save();

            if ($originalCompanyId && $originalCompanyId !== $purchase->company_id) {
                $oldCompany = Company::find($originalCompanyId);
                $this->syncSupplierPayeeBalance($oldCompany, -$originalGrandTotal);
                $this->syncSupplierPayeeBalance($purchase->company, $purchase->grand_total);
            } else {
                $delta = $purchase->grand_total - $originalGrandTotal;
                $this->syncSupplierPayeeBalance($purchase->company, $delta);
            }

            app(AutoPostingService::class)->updatePurchaseEntries($purchase);

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating purchase: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        try {
            DB::beginTransaction();
            $existingItems = PurchaseItem::withoutGlobalScopes()
                ->where('purchase_id', $purchase->id)
                ->get();

            // Revert stock updates
            foreach ($existingItems as $item) {
                $product = Product::find($item->product_id);
                $product->current_stock -= $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id ?? $purchase->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, -$item->quantity);
                    }
                }
            }

            // Delete purchase and items (cascading)
            $this->syncSupplierPayeeBalance($purchase->company, -($purchase->grand_total ?? 0));
            $purchase->delete();

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('purchases.index')->with('error', 'Error deleting purchase: ' . $e->getMessage());
        }
    }

    protected function syncSupplierPayeeBalance(?Company $company, float $delta): void
    {
        if (!$company) {
            return;
        }

        $payee = Payee::firstOrCreate(
            ['company_id' => $company->id],
            [
                'name' => $company->name,
                'type' => 'supplier',
                'category' => 'supplier',
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );

        if ($payee->name !== $company->name) {
            $payee->name = $company->name;
        }

        $payee->category = $payee->category ?: 'supplier';
        $payee->save();

        app(PayeeAccountService::class)->ensureAccountForPayee($payee);
    }

    /**
     * Get product details for AJAX request
     */
    public function getProductDetails($id)
    {
        $product = Product::with('company', 'category')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Create product via AJAX request
     */
    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'company_id' => 'required|exists:companies,id',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'opening_stock' => 'required|numeric|min:0',
        ]);

        try {
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'company_id' => $request->company_id,
                'category_id' => $request->category_id,
                'opening_stock' => $request->opening_stock,
                'current_stock' => $request->opening_stock,
                'purchase_price' => $request->purchase_price,
                'sale_price' => $request->sale_price,
            ]);

            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $defaultGodownId = Godown::defaultId();
                if ($defaultGodownId) {
                    $product->default_godown_id = $defaultGodownId;
                    $product->save();
                    GodownStockService::setStock($product->id, $defaultGodownId, $product->current_stock);
                }
            }

            $product->load('company', 'category');
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
