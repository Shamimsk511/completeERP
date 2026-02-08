<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Category;
use App\Models\Godown;
use App\Models\ErpFeatureSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ProductStockService;
use App\Services\GodownStockService;
use App\Exports\ProductTemplateExport;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Concerns\PreventsDuplicateSubmissions;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{   
   use PreventsDuplicateSubmissions;

   function __construct()
{
    $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index', 'show', 'getProducts', 'getProductGroupSummary']]);
    $this->middleware('permission:product-create', ['only' => ['create', 'store', 'checkDuplicateName']]);
    $this->middleware('permission:product-edit', ['only' => ['edit', 'update', 'searchProductsForMerge', 'getDuplicateProducts', 'mergeDuplicates']]);
    $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    $this->middleware('permission:product-import', ['only' => ['showImportForm', 'import', 'downloadTemplate']]);
    $this->middleware('permission:product-export', ['only' => ['export']]);
}

     public function index()
    {
    $companies = Company::brands()->orderBy('name')->get();
    $categories = Category::orderBy('name')->get();
    $godowns = ErpFeatureSetting::isEnabled('godown_management')
        ? Godown::orderBy('name')->get()
        : collect();
    
    return view('products.index', compact('companies', 'categories', 'godowns'));
    }
    
    /**
     * Process datatable ajax request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
/**
 * Process datatable ajax request.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getProducts(Request $request)
{
    if ($request->ajax()) {
        $query = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.company_id',
                'products.category_id',
                'products.current_stock',
                'products.purchase_price',
                'products.sale_price',
            ])
            ->with(['company:id,name', 'category:id,name']);
        $godownId = $request->get('godown_id');
        $useGodownStock = !empty($godownId) && ErpFeatureSetting::isEnabled('godown_management');

        if ($useGodownStock) {
            $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
                $join->on('products.id', '=', 'pgs.product_id')
                    ->where('pgs.godown_id', $godownId);
            })
            ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
            ->whereNotNull('pgs.product_id');
        }

        // Apply product ID filter
        if ($request->has('product_id') && $request->filled('product_id')) {
            $query->where('products.id', $request->product_id);
        }
        
        // Apply name filter
        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Apply company filter
        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }
        
        // Apply category filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Apply stock status filter
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            switch ($request->stock_status) {
                case 'in_stock':
                    if ($useGodownStock) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) > 10');
                    } else {
                        $query->where('current_stock', '>', 10);
                    }
                    break;
                case 'low_stock':
                    if ($useGodownStock) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) BETWEEN 1 AND 10');
                    } else {
                        $query->whereBetween('current_stock', [1, 10]);
                    }
                    break;
                case 'out_of_stock':
                    if ($useGodownStock) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) <= 0');
                    } else {
                        $query->where('current_stock', '<=', 0);
                    }
                    break;
            }
        }

        // Apply stock managed filter
        if ($request->has('stock_managed') && !empty($request->stock_managed)) {
            if ($request->stock_managed === 'managed') {
                $query->where(function($q) {
                    $q->where('is_stock_managed', true)
                      ->orWhereNull('is_stock_managed');
                });
            } elseif ($request->stock_managed === 'unmanaged') {
                $query->where('is_stock_managed', false);
            }
        }

        // Apply stock quantity range filters
        if ($request->has('min_stock') && $request->filled('min_stock')) {
            if ($useGodownStock) {
                $query->whereRaw('COALESCE(pgs.quantity, 0) >= ?', [$request->min_stock]);
            } else {
                $query->where('current_stock', '>=', $request->min_stock);
            }
        }

        if ($request->has('max_stock') && $request->filled('max_stock')) {
            if ($useGodownStock) {
                $query->whereRaw('COALESCE(pgs.quantity, 0) <= ?', [$request->max_stock]);
            } else {
                $query->where('current_stock', '<=', $request->max_stock);
            }
        }
        
        // Apply price range filters
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Apply purchase price range filters
        if ($request->has('min_purchase_price') && !empty($request->min_purchase_price)) {
            $query->where('purchase_price', '>=', $request->min_purchase_price);
        }

        if ($request->has('max_purchase_price') && !empty($request->max_purchase_price)) {
            $query->where('purchase_price', '<=', $request->max_purchase_price);
        }
        
        // Apply date range filter
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('company_name', function($row) {
                return $row->company->name ?? 'N/A';
            })
            ->addColumn('category_name', function($row) {
                return $row->category->name ?? 'N/A';
            })
            ->editColumn('current_stock', function($row) {
                return $row->godown_stock ?? $row->current_stock;
            })
            ->addColumn('action', function($row) {
                $editBtn = '<a href="' . route('products.edit', $row->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a> ';
                $deleteBtn = '<button type="button" class="btn btn-danger btn-sm delete-btn" data-url="' . route('products.destroy', $row->id) . '" data-name="' . e($row->name) . '"><i class="fas fa-trash"></i></button>';
                
                return $editBtn . $deleteBtn;
            })
            ->orderColumn('company_name', function($query, $order) {
                $direction = strtolower($order) === 'desc' ? 'desc' : 'asc';
                $query->orderByRaw('(select name from companies where companies.id = products.company_id) ' . $direction);
            })
            ->orderColumn('category_name', function($query, $order) {
                $direction = strtolower($order) === 'desc' ? 'desc' : 'asc';
                $query->orderByRaw('(select name from categories where categories.id = products.category_id) ' . $direction);
            })
            ->filterColumn('company_name', function($query, $keyword) {
                $query->whereHas('company', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('category_name', function($query, $keyword) {
                $query->whereHas('category', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    
    $companies = Company::brands()->orderBy('name')->get();
    $categories = Category::orderBy('name')->get();
    
    return response()->json([
        'error' => 'This endpoint only supports AJAX requests.'
    ], 400);
}

/**
 * Get grouped summary for categories or companies (for sidebar group browser).
 */
public function getProductGroupSummary(Request $request)
{
    if (!$request->ajax()) {
        return response()->json([
            'error' => 'This endpoint only supports AJAX requests.'
        ], 400);
    }

    $groupBy = $request->get('group_by', 'category');
    $query = Product::query();
    $godownId = $request->get('godown_id');
    $useGodownStock = !empty($godownId) && ErpFeatureSetting::isEnabled('godown_management');

    if ($useGodownStock) {
        $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
            $join->on('products.id', '=', 'pgs.product_id')
                ->where('pgs.godown_id', $godownId);
        })
        ->whereNotNull('pgs.product_id');
    }

    // Apply product ID filter
    if ($request->has('product_id') && $request->filled('product_id')) {
        $query->where('products.id', $request->product_id);
    }

    // Apply name filter
    if ($request->has('name') && !empty($request->name)) {
        $query->where('products.name', 'like', '%' . $request->name . '%');
    }

    // Apply company filter (skip when grouping by company so we show all companies)
    if ($groupBy !== 'company' && $request->has('company_id') && !empty($request->company_id)) {
        $query->where('products.company_id', $request->company_id);
    }

    // Apply category filter (skip when grouping by category so we show all categories)
    if ($groupBy !== 'category' && $request->has('category_id') && !empty($request->category_id)) {
        $query->where('products.category_id', $request->category_id);
    }

    // Apply stock status filter
    if ($request->has('stock_status') && !empty($request->stock_status)) {
        switch ($request->stock_status) {
            case 'in_stock':
                if ($useGodownStock) {
                    $query->whereRaw('COALESCE(pgs.quantity, 0) > 10');
                } else {
                    $query->where('current_stock', '>', 10);
                }
                break;
            case 'low_stock':
                if ($useGodownStock) {
                    $query->whereRaw('COALESCE(pgs.quantity, 0) BETWEEN 1 AND 10');
                } else {
                    $query->whereBetween('current_stock', [1, 10]);
                }
                break;
            case 'out_of_stock':
                if ($useGodownStock) {
                    $query->whereRaw('COALESCE(pgs.quantity, 0) <= 0');
                } else {
                    $query->where('current_stock', '<=', 0);
                }
                break;
        }
    }

    // Apply stock managed filter
    if ($request->has('stock_managed') && !empty($request->stock_managed)) {
        if ($request->stock_managed === 'managed') {
            $query->where(function($q) {
                $q->where('is_stock_managed', true)
                  ->orWhereNull('is_stock_managed');
            });
        } elseif ($request->stock_managed === 'unmanaged') {
            $query->where('is_stock_managed', false);
        }
    }

    // Apply stock quantity range filters
    if ($request->has('min_stock') && $request->filled('min_stock')) {
        if ($useGodownStock) {
            $query->whereRaw('COALESCE(pgs.quantity, 0) >= ?', [$request->min_stock]);
        } else {
            $query->where('current_stock', '>=', $request->min_stock);
        }
    }

    if ($request->has('max_stock') && $request->filled('max_stock')) {
        if ($useGodownStock) {
            $query->whereRaw('COALESCE(pgs.quantity, 0) <= ?', [$request->max_stock]);
        } else {
            $query->where('current_stock', '<=', $request->max_stock);
        }
    }

    // Apply price range filters
    if ($request->has('min_price') && !empty($request->min_price)) {
        $query->where('sale_price', '>=', $request->min_price);
    }

    if ($request->has('max_price') && !empty($request->max_price)) {
        $query->where('sale_price', '<=', $request->max_price);
    }

    // Apply purchase price range filters
    if ($request->has('min_purchase_price') && !empty($request->min_purchase_price)) {
        $query->where('purchase_price', '>=', $request->min_purchase_price);
    }

    if ($request->has('max_purchase_price') && !empty($request->max_purchase_price)) {
        $query->where('purchase_price', '<=', $request->max_purchase_price);
    }

    // Apply date range filter
    if ($request->has('start_date') && !empty($request->start_date)) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->has('end_date') && !empty($request->end_date)) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    if ($groupBy === 'company') {
        $query->leftJoin('companies as group_companies', 'products.company_id', '=', 'group_companies.id')
            ->selectRaw("products.company_id as group_id, COALESCE(group_companies.name, 'Unassigned') as group_name, COUNT(DISTINCT products.id) as product_count")
            ->groupBy('products.company_id', 'group_companies.name')
            ->orderBy('group_name', 'asc');
    } else {
        $query->leftJoin('categories as group_categories', 'products.category_id', '=', 'group_categories.id')
            ->selectRaw("products.category_id as group_id, COALESCE(group_categories.name, 'Unassigned') as group_name, COUNT(DISTINCT products.id) as product_count")
            ->groupBy('products.category_id', 'group_categories.name')
            ->orderBy('group_name', 'asc');
    }

    $groups = $query->get();

    return response()->json([
        'group_by' => $groupBy,
        'groups' => $groups,
    ]);
}

    public function create()
    {
        $companies = Company::brands()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        return view('products.create', compact('companies', 'categories', 'godowns'));
    }

public function store(Request $request)
{
    $rules = [
        'name' => 'required|string|max:255|unique:products,name',
        'description' => 'nullable|string',
        'company_id' => 'required|exists:companies,id',
        'category_id' => 'required|exists:categories,id',
        'opening_stock' => 'nullable|numeric|min:0',
        'purchase_price' => 'required|numeric|min:0',
        'sale_price' => 'required|numeric|min:0',
        'weight_value' => 'nullable|numeric|min:0',
        'weight_unit' => 'nullable|string|in:per_piece,per_box,per_unit',
    ];

    if (ErpFeatureSetting::isEnabled('godown_management')) {
        $rules['default_godown_id'] = 'nullable|exists:godowns,id';
    }

    $validated = $request->validate($rules, [
        'name.unique' => 'A product with this name already exists. Please use a different name or edit the existing product.',
    ]);

    if (!$this->claimIdempotency($request, 'product')) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate submission detected. Please wait and try again if needed.'
            ], 409);
        }
        return back()->withErrors(['duplicate' => 'Duplicate submission detected. Please wait and try again.'])->withInput();
    }

    $validated['is_stock_managed'] = $request->has('is_stock_managed') ? 1 : 0;

    // Set default opening_stock if not provided or if stock management is off
    if (!$validated['is_stock_managed'] || !isset($validated['opening_stock'])) {
        $validated['opening_stock'] = 0;
        $validated['current_stock'] = 0;
    }

    // Use service to handle stock logic
    $validated = ProductStockService::handleStockUpdate(new Product(), $validated);
    
    try {
        $product = Product::create($validated);
    } catch (\Exception $e) {
        $this->releaseIdempotency($request, 'product');
        throw $e;
    }

    if (ErpFeatureSetting::isEnabled('godown_management')) {
        $defaultGodownId = $validated['default_godown_id'] ?? Godown::defaultId();
        if ($defaultGodownId) {
            $product->default_godown_id = $defaultGodownId;
            $product->save();

            GodownStockService::setStock($product->id, $defaultGodownId, $product->current_stock);
        }
    }
    // If AJAX, return JSON
    if ($request->ajax()) {
        return response()->json($product->load('company', 'category'));
    }
     if ($request->has('save_and_new')) {
        return redirect()->route('products.create')
            ->with('success', 'Product created successfully! Ready to add another.');
    }
    return redirect()->route('products.index')->with('success', 'Product created successfully.');
}

    public function show(Request $request, Product $product)
    {
        $product->load(['company', 'category', 'defaultGodown', 'godownStocks.godown']);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $applyDateFilter = false;

        if ($startDate && $endDate) {
            try {
                $startDate = Carbon::parse($startDate)->startOfDay()->toDateString();
                $endDate = Carbon::parse($endDate)->endOfDay()->toDateString();
                $applyDateFilter = true;
            } catch (\Exception $e) {
                $startDate = null;
                $endDate = null;
            }
        } else {
            $startDate = null;
            $endDate = null;
        }

        $applyPurchaseDate = function ($query) use ($applyDateFilter, $startDate, $endDate) {
            if ($applyDateFilter) {
                $query->whereHas('purchase', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('purchase_date', [$startDate, $endDate]);
                });
            }
        };

        $applyInvoiceDate = function ($query) use ($applyDateFilter, $startDate, $endDate) {
            if ($applyDateFilter) {
                $query->whereHas('invoice', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('invoice_date', [$startDate, $endDate]);
                });
            }
        };

        $applyChallanDate = function ($query) use ($applyDateFilter, $startDate, $endDate) {
            if ($applyDateFilter) {
                $query->whereHas('challan', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('challan_date', [$startDate, $endDate]);
                });
            }
        };

        $applyOtherDeliveryDate = function ($query) use ($applyDateFilter, $startDate, $endDate) {
            if ($applyDateFilter) {
                $query->whereHas('otherDelivery', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('delivery_date', [$startDate, $endDate]);
                });
            }
        };

        // Load purchase history with related data
        $purchaseHistoryQuery = $product->purchaseItems()
            ->with(['purchase.company'])
            ->orderBy('created_at', 'desc');
        $applyPurchaseDate($purchaseHistoryQuery);
        if (!$applyDateFilter) {
            $purchaseHistoryQuery->take(50);
        }
        $purchaseHistory = $purchaseHistoryQuery->get();

        // Load sales history (invoice items) with related data
        $salesHistoryQuery = $product->invoiceItems()
            ->with(['invoice.customer'])
            ->orderBy('created_at', 'desc');
        $applyInvoiceDate($salesHistoryQuery);
        if (!$applyDateFilter) {
            $salesHistoryQuery->take(50);
        }
        $salesHistory = $salesHistoryQuery->get();

        // Load delivery history (challan items) with related data
        $deliveryHistoryQuery = $product->challanItems()
            ->with(['challan.invoice.customer'])
            ->orderBy('created_at', 'desc');
        $applyChallanDate($deliveryHistoryQuery);
        if (!$applyDateFilter) {
            $deliveryHistoryQuery->take(50);
        }
        $deliveryHistory = $deliveryHistoryQuery->get();

        // Load other delivery history
        $otherDeliveryHistoryQuery = $product->otherDeliveryItems()
            ->with(['otherDelivery'])
            ->orderBy('created_at', 'desc');
        $applyOtherDeliveryDate($otherDeliveryHistoryQuery);
        if (!$applyDateFilter) {
            $otherDeliveryHistoryQuery->take(50);
        }
        $otherDeliveryHistory = $otherDeliveryHistoryQuery->get();

        // Calculate movement summary
        $challanDeliveredQuery = $product->challanItems();
        $applyChallanDate($challanDeliveredQuery);
        $challanDelivered = (float) $challanDeliveredQuery->sum('quantity');

        $otherDeliveredQuery = $product->otherDeliveryItems();
        $applyOtherDeliveryDate($otherDeliveredQuery);
        $otherDelivered = (float) $otherDeliveredQuery->sum('quantity');

        $totalPurchasedQuery = $product->purchaseItems();
        $applyPurchaseDate($totalPurchasedQuery);
        $totalPurchased = (float) $totalPurchasedQuery->sum('quantity');

        $totalPurchaseValueQuery = $product->purchaseItems();
        $applyPurchaseDate($totalPurchaseValueQuery);
        $totalPurchaseValue = (float) $totalPurchaseValueQuery->sum('total_price');

        $totalSoldQuery = $product->invoiceItems();
        $applyInvoiceDate($totalSoldQuery);
        $totalSold = (float) $totalSoldQuery->sum('quantity');

        $totalSalesValueQuery = $product->invoiceItems();
        $applyInvoiceDate($totalSalesValueQuery);
        $totalSalesValue = (float) $totalSalesValueQuery->sum('total');

        $movementSummary = [
            'total_purchased' => $totalPurchased,
            'total_purchase_value' => $totalPurchaseValue,
            'total_sold' => $totalSold,
            'total_sales_value' => $totalSalesValue,
            'total_delivered' => $challanDelivered + $otherDelivered,
            'challan_delivered' => $challanDelivered,
            'other_delivered' => $otherDelivered,
            'pending_delivery' => $totalSold - $challanDelivered,
        ];

        $movementFilters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'has_filter' => $applyDateFilter,
        ];

        return view('products.show', compact(
            'product',
            'purchaseHistory',
            'salesHistory',
            'deliveryHistory',
            'otherDeliveryHistory',
            'movementSummary',
            'movementFilters'
        ));
    }

    public function edit(Product $product)
    {
        $companies = Company::brands()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        return view('products.edit', compact('product', 'companies', 'categories', 'godowns'));
    }

public function update(Request $request, Product $product)
{
    $rules = [
        'name' => 'required|string|max:255|unique:products,name,' . $product->id,
        'description' => 'nullable|string',
        'company_id' => 'required|exists:companies,id',
        'category_id' => 'required|exists:categories,id',
        'opening_stock' => 'nullable|numeric|min:0',
        'purchase_price' => 'required|numeric|min:0',
        'sale_price' => 'required|numeric|min:0',
        'weight_value' => 'nullable|numeric|min:0',
        'weight_unit' => 'nullable|string|in:per_piece,per_box,per_unit',
    ];

    if (ErpFeatureSetting::isEnabled('godown_management')) {
        $rules['default_godown_id'] = 'nullable|exists:godowns,id';
    }

    $validated = $request->validate($rules, [
        'name.unique' => 'A product with this name already exists. Please use a different name.',
    ]);

    // Handle stock management checkbox
    $validated['is_stock_managed'] = $request->has('is_stock_managed') ? 1 : 0;

    // Only calculate stock if stock management is enabled
    if ($validated['is_stock_managed']) {
        // Get opening_stock with default of 0
        $newOpeningStock = $validated['opening_stock'] ?? 0;
        // Calculate the difference in opening stock
        $openingStockDifference = $newOpeningStock - $product->opening_stock;
        // Adjust current stock based on the change in opening stock
        $validated['current_stock'] = $product->current_stock + $openingStockDifference;
        $validated['opening_stock'] = $newOpeningStock;
    } else {
        // For non-stock-managed products, set both to 0
        $validated['current_stock'] = 0;
        $validated['opening_stock'] = 0;
    }

    $product->update($validated);

    if (ErpFeatureSetting::isEnabled('godown_management')) {
        $defaultGodownId = $validated['default_godown_id'] ?? $product->default_godown_id ?? Godown::defaultId();
        if ($defaultGodownId) {
            if ($product->default_godown_id !== $defaultGodownId) {
                $product->default_godown_id = $defaultGodownId;
                $product->save();
            }

            if (!$product->godownStocks()->exists()) {
                GodownStockService::setStock($product->id, $defaultGodownId, $product->current_stock);
            } else {
                $product->godownStocks()->firstOrCreate([
                    'godown_id' => $defaultGodownId,
                ], [
                    'quantity' => 0,
                ]);
            }
        }
    }

    return redirect()->route('products.index')
        ->with('success', 'Product updated successfully.');
}

    public function destroy(Product $product)
    {
        $productId = $product->id;
        $usageMap = [
            'invoices' => DB::table('invoice_items')->where('product_id', $productId)->exists(),
            'purchases' => DB::table('purchase_items')->where('product_id', $productId)->exists(),
            'challans' => DB::table('challan_items')->where('product_id', $productId)->exists(),
            'returns' => DB::table('product_return_items')->where('product_id', $productId)->exists(),
            'other deliveries' => DB::table('other_delivery_items')->where('product_id', $productId)->exists(),
            'other delivery returns' => DB::table('other_delivery_return_items')->where('product_id', $productId)->exists(),
        ];

        $usedIn = array_keys(array_filter($usageMap));
        if (!empty($usedIn)) {
            $message = 'Cannot delete this product because it has transactions in: ' . implode(', ', $usedIn) . '.';

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 409);
            }

            return redirect()->route('products.index')
                ->with('error', $message);
        }

        $product->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

     public function showImportForm()
    {
        return view('products.import');
    }
    
    /**
     * Import products from Excel file
     */
    /**
 * Import products from Excel file
 */
public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);
    
    try {
        // Process the import
        $import = new ProductsImport();
        Excel::import($import, $request->file('file'));
        
        // Get results
        $results = $import->getResults();
        
        // Check for failures
        if (count($results['failures']) > 0) {
            return back()->with('import_failures', $results['failures'])
                ->with('warning', "Imported " . (string)$results['success'] . " products with " . (string)count($results['failures']) . " errors.");
        }
        
        return back()->with('success', (string)$results['success'] . " products imported successfully.");
    } catch (\Exception $e) {
        return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
    }
}

    
    /**
     * Generate and download product import template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'name',
            'description',
            'company_id',
            'category_id',
            'default_godown_id',
            'opening_stock',
            'is_stock_managed',
            'purchase_price',
            'sale_price',
            'weight_value',
            'weight_unit'
        ];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }
        
        // Add example data
        $exampleData = [
            'Example Product',
            'This is a sample description',
            '1',
            '1',
            '1',
            '10',
            '1',
            '100',
            '150',
            '1.25',
            'per_piece'
        ];
        
        foreach ($exampleData as $index => $value) {
            $sheet->setCellValue(chr(65 + $index) . '2', $value);
        }
        
        // Add company and category information
        $sheet->setCellValue('A4', 'Available Companies:');
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $row = 5;
        foreach ($companies as $company) {
            $sheet->setCellValue("A{$row}", "ID: {$company->id}");
            $sheet->setCellValue("B{$row}", "Name: {$company->name}");
            $row++;
        }
        
        $row += 1;
        $sheet->setCellValue("A{$row}", 'Available Categories:');
        $row++;
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        foreach ($categories as $category) {
            $sheet->setCellValue("A{$row}", "ID: {$category->id}");
            $sheet->setCellValue("B{$row}", "Name: {$category->name}");
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create response
        $writer = new Xlsx($spreadsheet);
        $filename = 'product_import_template.xlsx';
        
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
        }

public function reportsIndex()
{
    $companies = Company::brands()->orderBy('name')->get();
    $categories = Category::orderBy('name')->get();
    $godowns = ErpFeatureSetting::isEnabled('godown_management')
        ? Godown::orderBy('name')->get()
        : collect();
    
    return view('products.reports.index', compact('companies', 'categories', 'godowns'));
}

/**
 * Get stock report data via AJAX
 */
public function getStockReport(Request $request)
{
    if ($request->ajax()) {
        $query = Product::with(['company', 'category']);
        $godownId = $request->get('godown_id');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
                $join->on('products.id', '=', 'pgs.product_id')
                    ->where('pgs.godown_id', $godownId);
            })->select('products.*')
              ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
              ->whereNotNull('pgs.product_id');
        }
        
        // Apply filters
        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }
        
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            switch ($request->stock_status) {
                case 'in_stock':
                    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) > 10');
                    } else {
                        $query->where('current_stock', '>', 10);
                    }
                    break;
                case 'low_stock':
                    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) BETWEEN 1 AND 10');
                    } else {
                        $query->whereBetween('current_stock', [1, 10]);
                    }
                    break;
                case 'out_of_stock':
                    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) <= 0');
                    } else {
                        $query->where('current_stock', '<=', 0);
                    }
                    break;
            }
        }
        
        if ($request->has('min_stock') && !empty($request->min_stock)) {
            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $query->whereRaw('COALESCE(pgs.quantity, 0) >= ?', [$request->min_stock]);
            } else {
                $query->where('current_stock', '>=', $request->min_stock);
            }
        }
        
        if ($request->has('max_stock') && !empty($request->max_stock)) {
            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $query->whereRaw('COALESCE(pgs.quantity, 0) <= ?', [$request->max_stock]);
            } else {
                $query->where('current_stock', '<=', $request->max_stock);
            }
        }
        
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Get summary data
        $totalProducts = $query->count();
        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $inStockCount = (clone $query)->whereRaw('COALESCE(pgs.quantity, 0) > 10')->count();
            $lowStockCount = (clone $query)->whereRaw('COALESCE(pgs.quantity, 0) BETWEEN 1 AND 10')->count();
            $outOfStockCount = (clone $query)->whereRaw('COALESCE(pgs.quantity, 0) <= 0')->count();
            $totalStockQty = (clone $query)->sum(DB::raw('COALESCE(pgs.quantity, 0)'));
        } else {
            $inStockCount = (clone $query)->where('current_stock', '>', 10)->count();
            $lowStockCount = (clone $query)->whereBetween('current_stock', [1, 10])->count();
            $outOfStockCount = (clone $query)->where('current_stock', '<=', 0)->count();
            $totalStockQty = (clone $query)->sum('current_stock');
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('company_name', function($row) {
                return $row->company->name ?? 'N/A';
            })
            ->addColumn('category_name', function($row) {
                return $row->category->name ?? 'N/A';
            })
            ->addColumn('stock_status', function($row) {
                $stockValue = $row->godown_stock ?? $row->current_stock;
                if ($stockValue <= 0) {
                    return 'Out of Stock';
                } elseif ($stockValue <= 10) {
                    return 'Low Stock';
                } else {
                    return 'In Stock';
                }
            })
            ->addColumn('stock_difference', function($row) {
                $stockValue = $row->godown_stock ?? $row->current_stock;
                return $stockValue - $row->opening_stock;
            })
            ->editColumn('current_stock', function($row) {
                return $row->godown_stock ?? $row->current_stock;
            })
            ->filterColumn('company_name', function($query, $keyword) {
                $query->whereHas('company', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('category_name', function($query, $keyword) {
                $query->whereHas('category', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->with([
                'summary' => [
                    'total_products' => $totalProducts,
                    'in_stock_count' => $inStockCount,
                    'low_stock_count' => $lowStockCount,
                    'out_of_stock_count' => $outOfStockCount,
                    'total_stock_qty' => $totalStockQty
                ]
            ])
            ->make(true);
    }
}

/**
 * Get stock value report data via AJAX
 */
public function getStockValueReport(Request $request)
{
    if ($request->ajax()) {
        $query = Product::with(['company', 'category']);
        $godownId = $request->get('godown_id');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
                $join->on('products.id', '=', 'pgs.product_id')
                    ->where('pgs.godown_id', $godownId);
            })->select('products.*')
              ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
              ->whereNotNull('pgs.product_id');
        }
        
        // Apply same filters as stock report
        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }
        
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            switch ($request->stock_status) {
                case 'in_stock':
                    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) > 10');
                    } else {
                        $query->where('current_stock', '>', 10);
                    }
                    break;
                case 'low_stock':
                    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) BETWEEN 1 AND 10');
                    } else {
                        $query->whereBetween('current_stock', [1, 10]);
                    }
                    break;
                case 'out_of_stock':
                    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                        $query->whereRaw('COALESCE(pgs.quantity, 0) <= 0');
                    } else {
                        $query->where('current_stock', '<=', 0);
                    }
                    break;
            }
        }
        
        if ($request->has('min_stock') && !empty($request->min_stock)) {
            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $query->whereRaw('COALESCE(pgs.quantity, 0) >= ?', [$request->min_stock]);
            } else {
                $query->where('current_stock', '>=', $request->min_stock);
            }
        }
        
        if ($request->has('max_stock') && !empty($request->max_stock)) {
            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $query->whereRaw('COALESCE(pgs.quantity, 0) <= ?', [$request->max_stock]);
            } else {
                $query->where('current_stock', '<=', $request->max_stock);
            }
        }

        $priceType = $request->get('price_type', 'sale_price');
        if (!in_array($priceType, ['sale_price', 'purchase_price'], true)) {
            $priceType = 'sale_price';
        }
        
        if ($request->has('min_value') && !empty($request->min_value)) {
            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $query->whereRaw("COALESCE(pgs.quantity, 0) * {$priceType} >= ?", [$request->min_value]);
            } else {
                $query->whereRaw("current_stock * {$priceType} >= ?", [$request->min_value]);
            }
        }
        
        if ($request->has('max_value') && !empty($request->max_value)) {
            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $query->whereRaw("COALESCE(pgs.quantity, 0) * {$priceType} <= ?", [$request->max_value]);
            } else {
                $query->whereRaw("current_stock * {$priceType} <= ?", [$request->max_value]);
            }
        }
        
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Calculate summary data
        $products = $query->get();
        $totalProducts = $products->count();
        $totalStockValue = $products->sum(function($product) use ($priceType) {
            $stockValue = $product->godown_stock ?? $product->current_stock;
            return $stockValue * $product->$priceType;
        });
        $averageValue = $totalProducts > 0 ? $totalStockValue / $totalProducts : 0;
        $maxValue = $products->max(function($product) use ($priceType) {
            $stockValue = $product->godown_stock ?? $product->current_stock;
            return $stockValue * $product->$priceType;
        }) ?? 0;
        $minValue = $products->min(function($product) use ($priceType) {
            $stockValue = $product->godown_stock ?? $product->current_stock;
            return $stockValue * $product->$priceType;
        }) ?? 0;
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('company_name', function($row) {
                return $row->company->name ?? 'N/A';
            })
            ->addColumn('category_name', function($row) {
                return $row->category->name ?? 'N/A';
            })
            ->addColumn('stock_value_purchase', function($row) {
                $stockValue = $row->godown_stock ?? $row->current_stock;
                return $stockValue * $row->purchase_price;
            })
            ->addColumn('stock_value_sale', function($row) {
                $stockValue = $row->godown_stock ?? $row->current_stock;
                return $stockValue * $row->sale_price;
            })
            ->addColumn('potential_profit', function($row) {
                return ($row->current_stock * $row->sale_price) - ($row->current_stock * $row->purchase_price);
            })
            ->addColumn('profit_margin', function($row) {
                $purchaseValue = $row->current_stock * $row->purchase_price;
                if ($purchaseValue > 0) {
                    $saleValue = $row->current_stock * $row->sale_price;
                    return (($saleValue - $purchaseValue) / $purchaseValue) * 100;
                }
                return 0;
            })
            ->filterColumn('company_name', function($query, $keyword) {
                $query->whereHas('company', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('category_name', function($query, $keyword) {
                $query->whereHas('category', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->with([
                'summary' => [
                    'total_products' => $totalProducts,
                    'total_stock_value' => $totalStockValue,
                    'average_value' => $averageValue,
                    'max_value' => $maxValue,
                    'min_value' => $minValue,
                    'price_type' => $priceType
                ]
            ])
            ->make(true);
    }
}

/**
 * Export stock report to Excel
 */
public function exportStockReport(Request $request)
{
    $query = Product::with(['company', 'category']);
    $godownId = $request->get('godown_id');
    $selectedGodown = null;

    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
        $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
            $join->on('products.id', '=', 'pgs.product_id')
                ->where('pgs.godown_id', $godownId);
        })->select('products.*')
          ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
          ->whereNotNull('pgs.product_id');

        $selectedGodown = Godown::find($godownId);
    }
    
    // Apply all filters from request
    if ($request->has('name') && !empty($request->name)) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }
    
    if ($request->has('company_id') && !empty($request->company_id)) {
        $query->where('company_id', $request->company_id);
    }
    
    if ($request->has('category_id') && !empty($request->category_id)) {
        $query->where('category_id', $request->category_id);
    }
    
    if ($request->has('stock_status') && !empty($request->stock_status)) {
        switch ($request->stock_status) {
            case 'in_stock':
                if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                    $query->whereRaw('COALESCE(pgs.quantity, 0) > 10');
                } else {
                    $query->where('current_stock', '>', 10);
                }
                break;
            case 'low_stock':
                if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                    $query->whereRaw('COALESCE(pgs.quantity, 0) BETWEEN 1 AND 10');
                } else {
                    $query->whereBetween('current_stock', [1, 10]);
                }
                break;
            case 'out_of_stock':
                if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                    $query->whereRaw('COALESCE(pgs.quantity, 0) <= 0');
                } else {
                    $query->where('current_stock', '<=', 0);
                }
                break;
        }
    }
    
    $products = $query->get();
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $headers = [
        'Product ID', 'Product Name', 'Company', 'Category', 
        'Opening Stock', 'Current Stock', 'Stock Difference', 
        'Stock Status', 'Purchase Price', 'Sale Price', 'Created Date'
    ];
    
    foreach ($headers as $index => $header) {
        $sheet->setCellValue(chr(65 + $index) . '1', $header);
    }
    
    // Add data
    $row = 2;
    foreach ($products as $product) {
        $stockValue = $product->godown_stock ?? $product->current_stock;
        $stockStatus = $stockValue <= 0 ? 'Out of Stock' : 
                      ($stockValue <= 10 ? 'Low Stock' : 'In Stock');
        
        $sheet->setCellValue("A{$row}", $product->id);
        $sheet->setCellValue("B{$row}", $product->name);
        $sheet->setCellValue("C{$row}", $product->company->name ?? 'N/A');
        $sheet->setCellValue("D{$row}", $product->category->name ?? 'N/A');
        $sheet->setCellValue("E{$row}", $product->opening_stock);
        $sheet->setCellValue("F{$row}", $stockValue);
        $sheet->setCellValue("G{$row}", $stockValue - $product->opening_stock);
        $sheet->setCellValue("H{$row}", $stockStatus);
        $sheet->setCellValue("I{$row}", $product->purchase_price);
        $sheet->setCellValue("J{$row}", $product->sale_price);
        $sheet->setCellValue("K{$row}", $product->created_at->format('Y-m-d'));
        
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $writer = new Xlsx($spreadsheet);
    $filename = 'stock_report_' . date('Y_m_d_H_i_s') . '.xlsx';
    
    $tempFile = tempnam(sys_get_temp_dir(), 'excel');
    $writer->save($tempFile);
    
    return response()->download($tempFile, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend();
}

/**
 * Export stock value report to Excel
 */
public function exportStockValueReport(Request $request)
{
    $query = Product::with(['company', 'category']);
    $godownId = $request->get('godown_id');

    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
        $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
            $join->on('products.id', '=', 'pgs.product_id')
                ->where('pgs.godown_id', $godownId);
        })->select('products.*')
          ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
          ->whereNotNull('pgs.product_id');
    }
    
    // Apply filters (same as getStockValueReport method)
    if ($request->has('name') && !empty($request->name)) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }
    
    if ($request->has('company_id') && !empty($request->company_id)) {
        $query->where('company_id', $request->company_id);
    }
    
    if ($request->has('category_id') && !empty($request->category_id)) {
        $query->where('category_id', $request->category_id);
    }
    
    $products = $query->get();
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $headers = [
        'Product ID', 'Product Name', 'Company', 'Category', 
        'Current Stock', 'Purchase Price', 'Sale Price',
        'Stock Value (Purchase)', 'Stock Value (Sale)', 
        'Potential Profit', 'Profit Margin %', 'Created Date'
    ];
    
    foreach ($headers as $index => $header) {
        $sheet->setCellValue(chr(65 + $index) . '1', $header);
    }
    
    // Add data
    $row = 2;
    foreach ($products as $product) {
        $stockValue = $product->godown_stock ?? $product->current_stock;
        $stockValuePurchase = $stockValue * $product->purchase_price;
        $stockValueSale = $stockValue * $product->sale_price;
        $potentialProfit = $stockValueSale - $stockValuePurchase;
        $profitMargin = $stockValuePurchase > 0 ? ($potentialProfit / $stockValuePurchase) * 100 : 0;
        
        $sheet->setCellValue("A{$row}", $product->id);
        $sheet->setCellValue("B{$row}", $product->name);
        $sheet->setCellValue("C{$row}", $product->company->name ?? 'N/A');
        $sheet->setCellValue("D{$row}", $product->category->name ?? 'N/A');
        $sheet->setCellValue("E{$row}", $stockValue);
        $sheet->setCellValue("F{$row}", $product->purchase_price);
        $sheet->setCellValue("G{$row}", $product->sale_price);
        $sheet->setCellValue("H{$row}", $stockValuePurchase);
        $sheet->setCellValue("I{$row}", $stockValueSale);
        $sheet->setCellValue("J{$row}", $potentialProfit);
        $sheet->setCellValue("K{$row}", number_format($profitMargin, 2));
        $sheet->setCellValue("L{$row}", $product->created_at->format('Y-m-d'));
        
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $writer = new Xlsx($spreadsheet);
    $filename = 'stock_value_report_' . date('Y_m_d_H_i_s') . '.xlsx';
    
    $tempFile = tempnam(sys_get_temp_dir(), 'excel');
    $writer->save($tempFile);
    
    return response()->download($tempFile, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend();
}

/**
 * Stock Adjustment Page
 */
public function stockAdjustment()
{
    $companies = Company::brands()->orderBy('name')->get();
    $categories = Category::orderBy('name')->get();
    $godowns = ErpFeatureSetting::isEnabled('godown_management')
        ? Godown::orderBy('name')->get()
        : collect();

    return view('products.stock-adjustment', compact('companies', 'categories', 'godowns'));
}

/**
 * Get products for stock adjustment DataTable
 */
public function getStockAdjustmentData(Request $request)
{
    $query = Product::with(['company', 'category'])
        ->where(function($q) {
            $q->where('is_stock_managed', true)
              ->orWhereNull('is_stock_managed');
        });

    $godownId = $request->get('godown_id');

    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
        $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
            $join->on('products.id', '=', 'pgs.product_id')
                ->where('pgs.godown_id', $godownId);
        })->select('products.*')
          ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
          ->whereNotNull('pgs.product_id');
    }

    // Apply company filter
    if ($request->has('company_id') && !empty($request->company_id)) {
        $query->where('company_id', $request->company_id);
    }

    // Apply category filter
    if ($request->has('category_id') && !empty($request->category_id)) {
        $query->where('category_id', $request->category_id);
    }

    // Apply name filter
    if ($request->has('name') && !empty($request->name)) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('company_name', function($row) {
            return $row->company->name ?? 'N/A';
        })
        ->addColumn('category_name', function($row) {
            return $row->category->name ?? 'N/A';
        })
        ->addColumn('stock_input', function($row) {
            $systemStock = $row->godown_stock ?? $row->current_stock;
            return '<input type="number" step="0.01" class="form-control form-control-sm stock-input"
                    data-product-id="' . $row->id . '"
                    data-system-stock="' . $systemStock . '"
                    placeholder="Physical Count">';
        })
        ->addColumn('difference', function($row) {
            return '<span class="difference-value" data-product-id="' . $row->id . '">-</span>';
        })
        ->editColumn('current_stock', function($row) {
            return $row->godown_stock ?? $row->current_stock;
        })
        ->rawColumns(['stock_input', 'difference'])
        ->make(true);
}

/**
 * Save stock adjustments
 */
public function saveStockAdjustment(Request $request)
{
    $request->validate([
        'adjustments' => 'required|array',
        'adjustments.*.product_id' => 'required|exists:products,id',
        'adjustments.*.physical_count' => 'required|numeric|min:0',
        'adjustments.*.system_stock' => 'required|numeric',
        'adjustments.*.difference' => 'required|numeric',
        'godown_id' => 'nullable|exists:godowns,id',
    ]);

    $totalAdded = 0;
    $totalRemoved = 0;
    $adjustedCount = 0;
    $godownId = $request->get('godown_id');

    foreach ($request->adjustments as $adjustment) {
        $product = Product::find($adjustment['product_id']);
        if ($product) {
            $difference = $adjustment['difference'];

            if ($difference > 0) {
                $totalAdded += $difference;
            } else {
                $totalRemoved += abs($difference);
            }

            if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
                $resolvedGodownId = GodownStockService::resolveGodownId($godownId, $product);
                if ($resolvedGodownId) {
                    GodownStockService::setStock($product->id, $resolvedGodownId, $adjustment['physical_count']);
                    $product->current_stock = $product->current_stock + $difference;
                }
            } else {
                $product->current_stock = $adjustment['physical_count'];
            }
            $product->save();
            $adjustedCount++;
        }
    }

    return response()->json([
        'success' => true,
        'message' => "Stock adjustment completed successfully!",
        'summary' => [
            'products_adjusted' => $adjustedCount,
            'total_added' => $totalAdded,
            'total_removed' => $totalRemoved,
        ]
    ]);
}

/**
 * Print stock count sheet
 */
public function printStockCount(Request $request)
{
    $query = Product::with(['company', 'category'])
        ->where(function($q) {
            $q->where('is_stock_managed', true)
              ->orWhereNull('is_stock_managed');
        })
        ->orderBy('name');

    $godownId = $request->get('godown_id');
    $selectedGodown = null;

    if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
        $query->leftJoin('product_godown_stocks as pgs', function ($join) use ($godownId) {
            $join->on('products.id', '=', 'pgs.product_id')
                ->where('pgs.godown_id', $godownId);
        })->select('products.*')
          ->addSelect(DB::raw('COALESCE(pgs.quantity, 0) as godown_stock'))
          ->whereNotNull('pgs.product_id');

        $selectedGodown = Godown::find($godownId);
    }

    // Apply company filter
    if ($request->has('company_id') && !empty($request->company_id)) {
        $query->where('company_id', $request->company_id);
    }

    // Apply category filter
    if ($request->has('category_id') && !empty($request->category_id)) {
        $query->where('category_id', $request->category_id);
    }

    $products = $query->get();

    return view('products.print-stock-count', compact('products', 'selectedGodown'));
}

/**
 * Search products for merge functionality
 */
public function searchProductsForMerge(Request $request)
{
    $search = $request->get('q', '');

    $products = Product::with(['company', 'category'])
        ->where('name', 'like', '%' . $search . '%')
        ->orWhere('id', $search)
        ->orderBy('name')
        ->limit(50)
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'company' => $product->company->name ?? 'N/A',
                'category' => $product->category->name ?? 'N/A',
                'current_stock' => $product->current_stock,
                'purchase_price' => $product->purchase_price,
                'sale_price' => $product->sale_price,
            ];
        });

    return response()->json($products);
}

/**
 * Check if a product name already exists
 */
public function checkDuplicateName(Request $request)
{
    $name = $request->get('name', '');
    $excludeId = $request->get('exclude_id', null);

    if (empty(trim($name))) {
        return response()->json([
            'exists' => false,
            'message' => ''
        ]);
    }

    // Normalize the name for comparison (case-insensitive, trimmed)
    $normalizedName = strtolower(trim(preg_replace('/\s+/', ' ', $name)));

    // Find products with similar names
    $query = Product::with(['company', 'category'])
        ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName]);

    // Exclude current product if editing
    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }

    $existingProducts = $query->get();

    if ($existingProducts->count() > 0) {
        $productList = $existingProducts->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'company' => $product->company->name ?? 'N/A',
                'category' => $product->category->name ?? 'N/A',
                'current_stock' => $product->current_stock,
            ];
        });

        return response()->json([
            'exists' => true,
            'count' => $existingProducts->count(),
            'message' => 'A product with this name already exists.',
            'products' => $productList
        ]);
    }

    return response()->json([
        'exists' => false,
        'message' => ''
    ]);
}

/**
 * Get all duplicate product groups with movement data
 */
public function getDuplicateProducts(Request $request)
{
    // Get all products with their movement counts
    $products = Product::with(['company', 'category'])
        ->withCount([
            'purchaseItems as purchase_count',
            'invoiceItems as invoice_count',
            'challanItems as challan_count',
            'returnItems as return_count',
            'otherDeliveryItems as other_delivery_count',
        ])
        ->orderBy('name')
        ->get();

    // Group products by normalized name (lowercase, trimmed, remove extra spaces)
    $duplicateGroups = [];

    foreach ($products as $product) {
        // Normalize the product name for comparison
        $normalizedName = strtolower(trim(preg_replace('/\s+/', ' ', $product->name)));

        if (!isset($duplicateGroups[$normalizedName])) {
            $duplicateGroups[$normalizedName] = [];
        }

        // Calculate total movement
        $totalMovement = $product->purchase_count + $product->invoice_count +
                        $product->challan_count + $product->return_count +
                        $product->other_delivery_count;

        $duplicateGroups[$normalizedName][] = [
            'id' => $product->id,
            'name' => $product->name,
            'company' => $product->company->name ?? 'N/A',
            'company_id' => $product->company_id,
            'category' => $product->category->name ?? 'N/A',
            'category_id' => $product->category_id,
            'current_stock' => $product->current_stock,
            'purchase_price' => $product->purchase_price,
            'sale_price' => $product->sale_price,
            'purchase_count' => $product->purchase_count,
            'invoice_count' => $product->invoice_count,
            'challan_count' => $product->challan_count,
            'return_count' => $product->return_count,
            'other_delivery_count' => $product->other_delivery_count,
            'total_movement' => $totalMovement,
        ];
    }

    // Filter only groups with more than 1 product (actual duplicates)
    $duplicateGroups = array_filter($duplicateGroups, function($group) {
        return count($group) > 1;
    });

    // Sort each group by total movement (descending) - most movement first (will be primary)
    foreach ($duplicateGroups as &$group) {
        usort($group, function($a, $b) {
            return $b['total_movement'] - $a['total_movement'];
        });
    }

    // Convert to indexed array and add group info
    $result = [];
    foreach ($duplicateGroups as $normalizedName => $group) {
        $result[] = [
            'normalized_name' => $normalizedName,
            'display_name' => $group[0]['name'], // Use first product's name as display
            'product_count' => count($group),
            'products' => $group,
            'primary_id' => $group[0]['id'], // Product with most movement
            'total_stock' => array_sum(array_column($group, 'current_stock')),
        ];
    }

    // Sort groups by product count (descending)
    usort($result, function($a, $b) {
        return $b['product_count'] - $a['product_count'];
    });

    return response()->json([
        'total_duplicate_groups' => count($result),
        'total_duplicate_products' => array_sum(array_column($result, 'product_count')),
        'groups' => $result
    ]);
}

/**
 * Merge duplicate products into a primary product
 */
public function mergeDuplicates(Request $request)
{
    $request->validate([
        'primary_product_id' => 'required|exists:products,id',
        'duplicate_product_ids' => 'required|array|min:1',
        'duplicate_product_ids.*' => 'exists:products,id|different:primary_product_id',
    ]);

    $primaryProductId = $request->primary_product_id;
    $duplicateProductIds = $request->duplicate_product_ids;

    // Ensure primary product is not in duplicates
    $duplicateProductIds = array_filter($duplicateProductIds, function($id) use ($primaryProductId) {
        return $id != $primaryProductId;
    });

    if (empty($duplicateProductIds)) {
        return response()->json([
            'success' => false,
            'message' => 'No valid duplicate products to merge.'
        ], 400);
    }

    try {
        \DB::beginTransaction();

        $primaryProduct = Product::findOrFail($primaryProductId);
        $duplicateProducts = Product::whereIn('id', $duplicateProductIds)->get();

        $totalStockAdded = 0;
        $mergedCount = 0;

        foreach ($duplicateProducts as $duplicate) {
            // Transfer PurchaseItems
            \DB::table('purchase_items')
                ->where('product_id', $duplicate->id)
                ->update(['product_id' => $primaryProductId]);

            // Transfer InvoiceItems
            \DB::table('invoice_items')
                ->where('product_id', $duplicate->id)
                ->update(['product_id' => $primaryProductId]);

            // Transfer ChallanItems
            \DB::table('challan_items')
                ->where('product_id', $duplicate->id)
                ->update(['product_id' => $primaryProductId]);

            // Transfer ProductReturnItems
            \DB::table('product_return_items')
                ->where('product_id', $duplicate->id)
                ->update(['product_id' => $primaryProductId]);

            // Transfer OtherDeliveryItems
            \DB::table('other_delivery_items')
                ->where('product_id', $duplicate->id)
                ->update(['product_id' => $primaryProductId]);

            // Add stock from duplicate to primary
            $totalStockAdded += $duplicate->current_stock;

            // Delete the duplicate product
            $duplicate->delete();
            $mergedCount++;
        }

        // Update primary product's current stock
        $primaryProduct->current_stock += $totalStockAdded;
        $primaryProduct->save();

        \DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Successfully merged {$mergedCount} product(s) into \"{$primaryProduct->name}\".",
            'summary' => [
                'merged_count' => $mergedCount,
                'stock_added' => $totalStockAdded,
                'new_total_stock' => $primaryProduct->current_stock,
            ]
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to merge products: ' . $e->getMessage()
        ], 500);
    }
}
}
