<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Challan;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ChallanItem;
use App\Models\InvoiceItem;
use App\Models\Godown;
use App\Models\ErpFeatureSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Services\GodownStockService;

class ChallanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:challan-list|challan-create|challan-edit|challan-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:challan-create', ['only' => ['create', 'store', 'quickStore']]);
        $this->middleware('permission:challan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:challan-delete', ['only' => ['destroy']]);
        $this->middleware('permission:challan-print', ['only' => ['print']]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $challans = Challan::with(['invoice.customer'])->select('challans.*');

            return DataTables::of($challans)
                ->addColumn('invoice_number', function ($challan) {
                    return $challan->invoice->invoice_number;
                })
                ->addColumn('customer_name', function ($challan) {
                    return $challan->invoice->customer->name;
                })
                ->addColumn('formatted_date', function ($challan) {
                    return $challan->challan_date->format('d-m-Y');
                })
                ->addColumn('delivered_time', function ($challan) {
                    return $challan->delivered_at ? $challan->delivered_at->format('d-m-Y H:i') : $challan->created_at->format('d-m-Y H:i');
                })
                ->addColumn('items_count', function ($challan) {
                    return $challan->items->count() . ' items';
                })
                ->addColumn('action', function ($challan) {
                    $actions = '<a href="' . route('challans.show', $challan) . '" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>';

                    $actions .= ' <a href="' . route('challans.edit', $challan) . '" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>';

                    $actions .= ' <a href="' . route('challans.print', $challan) . '" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>';

                    // Admin can delete any challan
                    if (Auth::user()->hasRole('Admin') || Auth::user()->can('challan-delete')) {
                        $actions .= ' <form action="' . route('challans.destroy', $challan) . '" method="POST" style="display: inline-block;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure? Stock will be restored.\')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';
                    }

                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('challans.index');
    }


    public function create(Request $request)
    {
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        $invoices = Invoice::with(['customer'])
            ->whereHas('items', function ($query) {
                $query->whereRaw('quantity > (SELECT COALESCE(SUM(challan_items.quantity), 0) FROM challan_items WHERE challan_items.invoice_item_id = invoice_items.id)');
            })
            ->latest()
            ->get();

        $invoice_id = $request->invoice_id ? (int) $request->invoice_id : null;
        $invoice = null;
        $invoiceItems = [];

        if ($invoice_id) {
            $invoice = Invoice::with(['customer', 'items.product.category'])->findOrFail($invoice_id);

            if (!$invoices->contains('id', $invoice->id)) {
                $invoices = $invoices->prepend($invoice);
            }

            // Get invoice items with remaining quantities
            $invoiceItems = $invoice->items->map(function ($item) {
                $deliveredQuantity = $item->getDeliveredQuantityAttribute();
                $remainingQuantity = $item->quantity - $deliveredQuantity;

                $item->remaining_quantity = $remainingQuantity;
                $item->delivered_quantity = $deliveredQuantity;
                $item->auto_description = $item->description ?: ($item->code ?: ($item->product->name ?? ''));

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $item->godowns = GodownStockService::getAvailableGodownsForProduct($item->product_id);
                    $item->recommended_godown_id = GodownStockService::getOldestGodownIdForProduct($item->product_id);
                }

                return $item;
            })->filter(function ($item) {
                return $item->remaining_quantity > 0;
            });
        }

        $challan_number = Challan::getNextChallanNumber();
        $challan_date = Carbon::now()->format('Y-m-d');

        return view('challans.create', compact('invoices', 'invoice', 'invoiceItems', 'challan_number', 'challan_date', 'invoice_id', 'godowns'));
    }

    public function getInvoiceItems($invoice_id)
    {
        $invoice = Invoice::with(['customer', 'items.product.category'])->findOrFail($invoice_id);

        // Calculate remaining quantities
        $invoiceItems = $invoice->items->map(function ($item) {
            $deliveredQuantity = $item->getDeliveredQuantityAttribute();
            $remainingQuantity = $item->quantity - $deliveredQuantity;

            $godownOptions = collect();
            $recommendedGodownId = null;
            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $godownOptions = GodownStockService::getAvailableGodownsForProduct($item->product_id);
                $recommendedGodownId = GodownStockService::getOldestGodownIdForProduct($item->product_id);
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'description' => $item->description ?: ($item->code ?: $item->product->name),
                'code' => $item->code,
                'quantity' => $item->quantity,
                'boxes' => $item->boxes,
                'pieces' => $item->pieces,
                'delivered_quantity' => $deliveredQuantity,
                'remaining_quantity' => $remainingQuantity,
                'godowns' => $godownOptions,
                'recommended_godown_id' => $recommendedGodownId,
                'category' => $item->product->category ? [
                    'name' => $item->product->category->name,
                    'box_pcs' => $item->product->category->box_pcs,
                    'pieces_feet' => $item->product->category->pieces_feet,
                ] : null,
            ];
        })->filter(function ($item) {
            return $item['remaining_quantity'] > 0;
        })->values();

        return response()->json([
            'invoice' => $invoice,
            'items' => $invoiceItems
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'invoice_id' => 'required|exists:invoices,id',
            'challan_number' => 'required|string|unique:challans',
            'challan_date' => 'required|date',
            'vehicle_number' => 'nullable|string|max:20',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'shipping_address' => 'required|string',
            'receiver_name' => 'required|string|max:100',
            'receiver_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'invoice_item_id' => 'required|array',
            'invoice_item_id.*' => 'exists:invoice_items,id',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'godown_id' => 'nullable|array',
            'godown_id.*' => 'nullable|exists:godowns,id',
            'description' => 'required|array',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'boxes' => 'nullable|array',
            'pieces' => 'nullable|array',
        ];

        if (!ErpFeatureSetting::isEnabled('godown_management')) {
            unset($rules['godown_id'], $rules['godown_id.*']);
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Create challan
            $challan = Challan::create([
                'challan_number' => $request->challan_number,
                'invoice_id' => $request->invoice_id,
                'challan_date' => $request->challan_date,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'shipping_address' => $request->shipping_address,
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'notes' => $request->notes,
                'delivered_at' => now(), // Mark as delivered immediately
            ]);

            // Create challan items and adjust stock immediately
            for ($i = 0; $i < count($request->invoice_item_id); $i++) {
                if (isset($request->quantity[$i]) && $request->quantity[$i] > 0) {
                    $invoiceItemId = $request->invoice_item_id[$i];
                    $invoiceItem = InvoiceItem::findOrFail($invoiceItemId);

                    // Calculate remaining quantity
                    $deliveredQuantity = $invoiceItem->getDeliveredQuantityAttribute();
                    $remainingQuantity = $invoiceItem->quantity - $deliveredQuantity;

                    // Validate if requested quantity is valid
                    if ($request->quantity[$i] > $remainingQuantity) {
                        throw new \Exception("Challan quantity cannot exceed remaining quantity for item #{$i}");
                    }

                    // Create challan item
                    $product = Product::findOrFail($request->product_id[$i]);
                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $requestedGodownId = $request->godown_id[$i] ?? null;
                        $godownId = $requestedGodownId ?: GodownStockService::getOldestGodownIdForProduct($product->id);
                        $godownId = GodownStockService::resolveGodownId($godownId, $product);
                    }

                    $preventNegative = ErpFeatureSetting::isEnabled('prevent_negative_stock');
                    $isStockManaged = $product->is_stock_managed !== false;
                    if ($preventNegative && $isStockManaged) {
                        if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                            $availableStock = GodownStockService::getAvailableStock($product->id, $godownId);
                            if ($availableStock < $request->quantity[$i]) {
                                throw new \Exception("Insufficient stock in selected godown for item #{$i}");
                            }
                        } else {
                            if ($product->current_stock < $request->quantity[$i]) {
                                throw new \Exception("Insufficient stock for item #{$i}");
                            }
                        }
                    }

                    $preventNegative = ErpFeatureSetting::isEnabled('prevent_negative_stock');
                    $isStockManaged = $product->is_stock_managed !== false;
                    if ($preventNegative && $isStockManaged) {
                        if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                            $availableStock = GodownStockService::getAvailableStock($product->id, $godownId);
                            if ($availableStock < $request->quantity[$i]) {
                                throw new \Exception("Insufficient stock in selected godown for item #{$i}");
                            }
                        } else {
                            if ($product->current_stock < $request->quantity[$i]) {
                                throw new \Exception("Insufficient stock for item #{$i}");
                            }
                        }
                    }

                    $description = $request->description[$i] ?? null;
                    $description = is_string($description) ? trim($description) : $description;
                    if (!$description) {
                        $description = $invoiceItem->description
                            ?: ($invoiceItem->code ?: ($product->name ?? ''));
                    }

                    ChallanItem::create([
                        'challan_id' => $challan->id,
                        'invoice_item_id' => $invoiceItemId,
                        'product_id' => $request->product_id[$i],
                        'godown_id' => $godownId,
                        'description' => $description,
                        'quantity' => $request->quantity[$i],
                        'boxes' => $request->boxes[$i] ?? null,
                        'pieces' => $request->pieces[$i] ?? null,
                    ]);

                    // Reduce stock immediately
                    $product->decrement('current_stock', $request->quantity[$i]);

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, -$request->quantity[$i]);
                    }
                }
            }

            // Update invoice delivery status
            $this->updateInvoiceDeliveryStatus($challan->invoice);

            DB::commit();

            return redirect()->route('challans.show', $challan)
                ->with('success', 'Challan created successfully. Stock has been adjusted.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating challan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Quick store challan from invoice delivery modal
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'vehicle_number' => 'nullable|string|max:20',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string',
            'receiver_name' => 'nullable|string|max:100',
            'receiver_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($request->invoice_id);

        DB::beginTransaction();
        try {
            // Create challan with delivery info (or defaults)
            $challan = Challan::create([
                'challan_number' => Challan::getNextChallanNumber(),
                'invoice_id' => $invoice->id,
                'challan_date' => now()->toDateString(),
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'shipping_address' => $request->shipping_address ?: $invoice->customer->address ?: 'N/A',
                'receiver_name' => $request->receiver_name ?: $invoice->customer->name,
                'receiver_phone' => $request->receiver_phone ?: $invoice->customer->phone,
                'notes' => $request->notes ?: 'Auto-generated challan for invoice #' . $invoice->invoice_number,
                'delivered_at' => now(),
            ]);

            // Add all remaining items to challan
            foreach ($invoice->items as $item) {
                $deliveredQuantity = $item->getDeliveredQuantityAttribute();
                $remainingQuantity = $item->quantity - $deliveredQuantity;

                if ($remainingQuantity > 0) {
                    // Create challan item with same box/pcs as invoice
                    $product = Product::findOrFail($item->product_id);
                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $godownId = GodownStockService::getOldestGodownIdForProduct($product->id);
                    }

                    $preventNegative = ErpFeatureSetting::isEnabled('prevent_negative_stock');
                    $isStockManaged = $product->is_stock_managed !== false;
                    if ($preventNegative && $isStockManaged) {
                        if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                            $availableStock = GodownStockService::getAvailableStock($product->id, $godownId);
                            if ($availableStock < $remainingQuantity) {
                                throw new \Exception("Insufficient stock in selected godown for product: {$product->name}");
                            }
                        } else {
                            if ($product->current_stock < $remainingQuantity) {
                                throw new \Exception("Insufficient stock for product: {$product->name}");
                            }
                        }
                    }

                    ChallanItem::create([
                        'challan_id' => $challan->id,
                        'invoice_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'godown_id' => $godownId,
                        'description' => $item->code ?: $item->description ?: $item->product->name,
                        'quantity' => $remainingQuantity,
                        'boxes' => $item->boxes,
                        'pieces' => $item->pieces,
                    ]);

                    // Reduce stock immediately
                    $product->decrement('current_stock', $remainingQuantity);

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, -$remainingQuantity);
                    }
                }
            }

            // Update invoice delivery status to delivered
            $invoice->delivery_status = 'delivered';
            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery completed. Challan #' . $challan->challan_number . ' created.',
                'challan_id' => $challan->id,
                'challan_number' => $challan->challan_number,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating challan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Challan $challan)
    {
        $challan->load(['invoice.customer', 'items.product.category', 'items.godown']);
        return view('challans.show', compact('challan'));
    }

    public function edit(Challan $challan)
    {
        $challan->load(['invoice.customer', 'items.product.category', 'items.invoiceItem']);
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();

        // Calculate remaining quantities for invoice items (excluding current challan)
        $invoice = $challan->invoice;
        $invoiceItems = $invoice->items->map(function ($item) use ($challan) {
            $deliveredQuantity = $item->challanItems()
                ->whereHas('challan', function ($query) use ($challan) {
                    $query->where('id', '!=', $challan->id);
                })
                ->sum('quantity');

            $remainingQuantity = $item->quantity - $deliveredQuantity;

            // Add what's in the current challan
            $currentChallanQuantity = $item->challanItems()
                ->where('challan_id', $challan->id)
                ->sum('quantity');

            $item->remaining_quantity = $remainingQuantity;
            $item->current_challan_quantity = $currentChallanQuantity;
            $item->max_quantity = $remainingQuantity + $currentChallanQuantity;

            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $godownOptions = GodownStockService::getAvailableGodownsForProduct($item->product_id);
                $currentGodownId = optional($challan->items->firstWhere('invoice_item_id', $item->id))->godown_id;

                if ($currentGodownId && !$godownOptions->contains('id', $currentGodownId)) {
                    $currentGodown = Godown::find($currentGodownId);
                    if ($currentGodown) {
                        $godownOptions->push((object) [
                            'id' => $currentGodown->id,
                            'name' => $currentGodown->name,
                            'location' => $currentGodown->location,
                            'stock' => GodownStockService::getAvailableStock($item->product_id, $currentGodownId),
                        ]);
                    }
                }

                $item->godowns = $godownOptions;
                $item->recommended_godown_id = GodownStockService::getOldestGodownIdForProduct($item->product_id);
            }

            return $item;
        });

        return view('challans.edit', compact('challan', 'invoice', 'invoiceItems', 'godowns'));
    }

    public function update(Request $request, Challan $challan)
    {
        $rules = [
            'challan_date' => 'required|date',
            'vehicle_number' => 'nullable|string|max:20',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'shipping_address' => 'required|string',
            'receiver_name' => 'required|string|max:100',
            'receiver_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'invoice_item_id' => 'required|array',
            'invoice_item_id.*' => 'exists:invoice_items,id',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'godown_id' => 'nullable|array',
            'godown_id.*' => 'nullable|exists:godowns,id',
            'description' => 'nullable|array',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'boxes' => 'nullable|array',
            'pieces' => 'nullable|array',
        ];

        if (!ErpFeatureSetting::isEnabled('godown_management')) {
            unset($rules['godown_id'], $rules['godown_id.*']);
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $existingItems = ChallanItem::withoutGlobalScopes()
                ->where('challan_id', $challan->id)
                ->get();

            // Restore stock for old items
            foreach ($existingItems as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->increment('current_stock', $oldItem->quantity);

                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $resolvedGodownId = GodownStockService::resolveGodownId($oldItem->godown_id, $product);
                        if ($resolvedGodownId) {
                            GodownStockService::adjustStock($product->id, $resolvedGodownId, $oldItem->quantity);
                        }
                    }
                }
            }

            // Update challan
            $challan->update([
                'challan_date' => $request->challan_date,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'shipping_address' => $request->shipping_address,
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'notes' => $request->notes,
            ]);

            // Delete existing challan items
            ChallanItem::withoutGlobalScopes()->where('challan_id', $challan->id)->delete();

            // Create new challan items and reduce stock
            for ($i = 0; $i < count($request->invoice_item_id); $i++) {
                if (isset($request->quantity[$i]) && $request->quantity[$i] > 0) {
                    $invoiceItemId = $request->invoice_item_id[$i];
                    $invoiceItem = InvoiceItem::findOrFail($invoiceItemId);

                    // Calculate remaining quantity (excluding current challan which we just deleted)
                    $deliveredQuantity = $invoiceItem->challanItems()
                        ->whereHas('challan', function ($query) use ($challan) {
                            $query->where('id', '!=', $challan->id);
                        })
                        ->sum('quantity');

                    $remainingQuantity = $invoiceItem->quantity - $deliveredQuantity;

                    // Validate if requested quantity is valid
                    if ($request->quantity[$i] > $remainingQuantity) {
                        throw new \Exception("Challan quantity cannot exceed remaining quantity for item #{$i}");
                    }

                    // Create challan item
                    $product = Product::findOrFail($request->product_id[$i]);
                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $requestedGodownId = $request->godown_id[$i] ?? null;
                        $godownId = $requestedGodownId ?: GodownStockService::getOldestGodownIdForProduct($product->id);
                        $godownId = GodownStockService::resolveGodownId($godownId, $product);
                    }

                    $description = $request->description[$i] ?? null;
                    $description = is_string($description) ? trim($description) : $description;
                    if (!$description) {
                        $description = $invoiceItem->description
                            ?: ($invoiceItem->code ?: ($product->name ?? ''));
                    }

                    ChallanItem::create([
                        'challan_id' => $challan->id,
                        'invoice_item_id' => $invoiceItemId,
                        'product_id' => $request->product_id[$i],
                        'godown_id' => $godownId,
                        'description' => $description,
                        'quantity' => $request->quantity[$i],
                        'boxes' => $request->boxes[$i] ?? null,
                        'pieces' => $request->pieces[$i] ?? null,
                    ]);

                    // Reduce stock for new quantity
                    $product->decrement('current_stock', $request->quantity[$i]);

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, -$request->quantity[$i]);
                    }
                }
            }

            // Update invoice delivery status
            $this->updateInvoiceDeliveryStatus($challan->invoice);

            DB::commit();
            return redirect()->route('challans.show', $challan)
                ->with('success', 'Challan updated successfully. Stock has been adjusted.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating challan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Challan $challan)
    {
        // Only admin can delete challans
        if (!Auth::user()->hasRole('Admin') && !Auth::user()->can('challan-delete')) {
            return redirect()->route('challans.index')
                ->with('error', 'You do not have permission to delete challans.');
        }

        DB::beginTransaction();
        try {
            $existingItems = ChallanItem::withoutGlobalScopes()
                ->where('challan_id', $challan->id)
                ->get();

            // Restore product stock
            foreach ($existingItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('current_stock', $item->quantity);

                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                        if ($resolvedGodownId) {
                            GodownStockService::adjustStock($product->id, $resolvedGodownId, $item->quantity);
                        }
                    }
                }
            }

            // Get the associated invoice before deleting the challan
            $invoice = $challan->invoice;

            // Delete the challan
            $challan->delete();

            // Update the invoice delivery status
            if ($invoice) {
                $this->updateInvoiceDeliveryStatus($invoice);
            }

            DB::commit();

            return redirect()->route('challans.index')
                ->with('success', 'Challan deleted successfully. Stock has been restored.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Challan deletion failed: ' . $e->getMessage());
        }
    }

    public function print(Challan $challan)
    {
        $challan->load(['invoice.customer', 'items.product.category', 'items.godown']);
        return view('challans.print', compact('challan'));
    }

    protected function updateInvoiceDeliveryStatus(Invoice $invoice)
    {
        $invoice->refresh();

        $allDelivered = true;
        $anyDelivered = false;

        foreach ($invoice->items as $item) {
            $deliveredQuantity = $item->getDeliveredQuantityAttribute();

            if ($deliveredQuantity > 0) {
                $anyDelivered = true;
            }

            if ($deliveredQuantity < $item->quantity) {
                $allDelivered = false;
            }
        }

        if ($allDelivered) {
            $invoice->delivery_status = 'delivered';
        } elseif ($anyDelivered) {
            $invoice->delivery_status = 'partial';
        } else {
            $invoice->delivery_status = 'pending';
        }

        $invoice->save();
    }
}
