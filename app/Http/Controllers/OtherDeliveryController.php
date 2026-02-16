<?php

namespace App\Http\Controllers;

use App\Models\OtherDelivery;
use App\Models\OtherDeliveryItem;
use App\Models\Product;
use App\Models\ErpFeatureSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\GodownStockService;

class OtherDeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:other-delivery-list|other-delivery-create|other-delivery-edit|other-delivery-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:other-delivery-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:other-delivery-edit', ['only' => ['edit', 'update', 'updateStatus']]);
        $this->middleware('permission:other-delivery-delete', ['only' => ['destroy']]);
        $this->middleware('permission:other-delivery-print', ['only' => ['print']]);
    }

    public function index(Request $request)
    {
        // Check if this is an AJAX request for DataTables
        if ($request->ajax()) {
            $deliveries = OtherDelivery::with(['deliveredBy'])
                ->select(['id', 'challan_number', 'delivery_date', 'recipient_name', 'status', 'delivered_by'])
                ->latest();
            
            // Apply filters if provided
            if ($request->has('status') && $request->status != '') {
                $deliveries->where('status', $request->status);
            }
            
            if ($request->has('start_date') && $request->start_date != '') {
                $deliveries->whereDate('delivery_date', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date != '') {
                $deliveries->whereDate('delivery_date', '<=', $request->end_date);
            }
            
            $deliveries = $deliveries->get();
            
            // FIXED: Return data as arrays instead of objects for DataTables compatibility
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $deliveries->count(),
                'recordsFiltered' => $deliveries->count(),
                'data' => $deliveries->map(function($delivery) {
                    return [
                        $delivery->id,
                        $delivery->challan_number,
                        $delivery->delivery_date->format('d-m-Y'),
                        $delivery->recipient_name,
                        $this->getStatusBadge($delivery->status),
                        $delivery->deliveredBy ? $delivery->deliveredBy->name : 'Not assigned',
                        $this->getActionButtons($delivery)
                    ];
                })->values() // Ensure it's a proper array
            ]);
        }
        
        // Regular page load
        $deliveries = OtherDelivery::with(['deliveredBy'])->latest()->get();
        $deliveryPersonnel = \App\Models\User::orderBy('name')->get();
        
        return view('other-deliveries.index', compact('deliveries', 'deliveryPersonnel'));
    }

    // FIXED: Added helper method for status badge
    private function getStatusBadge($status)
    {
        switch($status) {
            case 'pending':
                return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>';
            case 'delivered':
                return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Delivered</span>';
            case 'in_transit':
                return '<span class="badge badge-info"><i class="fas fa-truck"></i> In Transit</span>';
            case 'cancelled':
                return '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Cancelled</span>';
            default:
                return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
        }
    }

    private function getActionButtons($delivery)
    {
        $buttons = '<div class="action-buttons">';
        
        // View button
        $buttons .= '<a href="' . route('other-deliveries.show', $delivery) . '" class="btn modern-btn-sm modern-btn-info" title="View Details"><i class="fas fa-eye"></i></a>';
        
        // Status dropdown
        $buttons .= '<div class="status-dropdown-container">';
        $buttons .= '<div class="status-badge-clickable" data-delivery-id="' . $delivery->id . '" data-current-status="' . $delivery->status . '">';
        
        if ($delivery->status == 'pending') {
            $buttons .= '<span class="badge badge-warning status-badge"><i class="fas fa-clock"></i> Pending <i class="fas fa-chevron-down status-arrow"></i></span>';
        } elseif ($delivery->status == 'delivered') {
            $buttons .= '<span class="badge badge-success status-badge"><i class="fas fa-check-circle"></i> Delivered <i class="fas fa-chevron-down status-arrow"></i></span>';
        } elseif ($delivery->status == 'in_transit') {
            $buttons .= '<span class="badge badge-info status-badge"><i class="fas fa-truck"></i> In Transit <i class="fas fa-chevron-down status-arrow"></i></span>';
        } else {
            $buttons .= '<span class="badge badge-danger status-badge"><i class="fas fa-times-circle"></i> Cancelled <i class="fas fa-chevron-down status-arrow"></i></span>';
        }
        
        $buttons .= '</div>';
        $buttons .= '<div class="status-dropdown-menu" style="display: none;">';
        $buttons .= '<div class="status-option" data-status="pending"><i class="fas fa-clock"></i> Pending</div>';
        $buttons .= '<div class="status-option" data-status="in_transit"><i class="fas fa-truck"></i> In Transit</div>';
        $buttons .= '<div class="status-option" data-status="delivered"><i class="fas fa-check-circle"></i> Delivered</div>';
        $buttons .= '<div class="status-option" data-status="cancelled"><i class="fas fa-times-circle"></i> Cancelled</div>';
        $buttons .= '</div></div>';
        
        // Edit and Delete buttons (only if not delivered)
        if ($delivery->status != 'delivered') {
            $buttons .= '<a href="' . route('other-deliveries.edit', $delivery) . '" class="btn modern-btn-sm modern-btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
            $buttons .= '<button type="button" class="btn modern-btn-sm modern-btn-danger delete-delivery" data-delivery-id="' . $delivery->id . '" data-challan="' . $delivery->challan_number . '" title="Delete"><i class="fas fa-trash"></i></button>';
        }
        
        // Print button
        $buttons .= '<a href="' . route('other-deliveries.print', $delivery) . '" class="btn modern-btn-sm modern-btn-secondary" target="_blank" title="Print Challan"><i class="fas fa-print"></i></a>';
        
        $buttons .= '</div>';
        
        return $buttons;
    }

    public function create()
    {
        $products = Product::where('current_stock', '>', 0)->orderBy('name')->get();
        $challan_number = OtherDelivery::getNextChallanNumber();
        $delivery_date = Carbon::now()->format('Y-m-d');
        
        // Get unique recipients from previous deliveries
        $recipients = OtherDelivery::select('recipient_name', 'recipient_phone', 'recipient_address')
                                  ->distinct()
                                  ->orderBy('recipient_name')
                                  ->get();
        
        return view('other-deliveries.create', compact('products', 'challan_number', 'delivery_date', 'recipients'));
    }

    public function store(Request $request)
    {
        // DEBUGGING: Log all request data
        \Log::info('=== DELIVERY FORM SUBMISSION DEBUG ===');
        \Log::info('All Request Data:', ['data' => $request->all()]);
        \Log::info('Product IDs:', ['product_ids' => $request->product_id]);
        \Log::info('Quantities:', ['quantities' => $request->quantity]);
        \Log::info('Request Method:', ['method' => $request->method()]);
        \Log::info('Content Type:', ['content_type' => $request->header('Content-Type')]);
        
        // DEBUGGING: Check for empty arrays
        if (empty($request->product_id)) {
            \Log::error('ERROR: No product_id array received');
            return redirect()->back()
                ->with('error', 'DEBUG: No products received in request')
                ->withInput();
        }
        
        if (empty($request->quantity)) {
            \Log::error('ERROR: No quantity array received');
            return redirect()->back()
                ->with('error', 'DEBUG: No quantities received in request')
                ->withInput();
        }

        try {
            $validated = $request->validate([
                'challan_number' => ['required', 'string', $this->tenantUniqueRule('other_deliveries', 'challan_number')],
                'delivery_date' => 'required|date',
                'recipient_name' => 'required|string|max:255',
                'recipient_address' => 'required|string',
                'recipient_phone' => 'nullable|string|max:20',
                'vehicle_type' => 'nullable|string|max:50',
                'vehicle_number' => 'nullable|string|max:20',
                'driver_name' => 'nullable|string|max:100',
                'driver_phone' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
                'delivery_status' => 'required|in:pending,in_transit,delivered,cancelled',
                'product_id' => 'required|array',
                'product_id.*' => 'exists:products,id',
                'description' => 'nullable|array',
                'quantity' => 'required|array',
                'quantity.*' => 'numeric|min:0.01',
                'cartons' => 'nullable|array',
                'pieces' => 'nullable|array',
                'create_and_new' => 'nullable|boolean',
            ]);
            
            \Log::info('Validation passed');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', ['errors' => $e->errors()]);
            return redirect()->back()
                ->with('error', 'Validation failed: ' . json_encode($e->errors()))
                ->withInput();
        }

        DB::beginTransaction();
        try {
            \Log::info('Creating delivery record...');
            
            // Create delivery
            $delivery = OtherDelivery::create([
                'challan_number' => $request->challan_number,
                'delivery_date' => $request->delivery_date,
                'recipient_name' => $request->recipient_name,
                'recipient_address' => $request->recipient_address,
                'recipient_phone' => $request->recipient_phone,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'notes' => $request->notes,
                'status' => $request->delivery_status ?? 'pending',
                'delivered_by' => Auth::id(),
            ]);
            
            \Log::info('Delivery created with ID:', ['delivery_id' => $delivery->id]);
            
            // Create delivery items and update stock
            $itemCount = 0;
            for ($i = 0; $i < count($request->product_id); $i++) {
                if (isset($request->quantity[$i]) && $request->quantity[$i] > 0) {
                    $productId = $request->product_id[$i];
                    $product = Product::findOrFail($productId);
                    
                    \Log::info('Processing item:', [
                        'index' => $i,
                        'product_id' => $productId,
                        'quantity' => $request->quantity[$i]
                    ]);
                    
                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $godownId = GodownStockService::resolveGodownId(null, $product);
                    }

                    $preventNegative = ErpFeatureSetting::isEnabled('prevent_negative_stock');
                    $isStockManaged = $product->is_stock_managed !== false;
                    if ($preventNegative && $isStockManaged) {
                        if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                            $availableStock = GodownStockService::getAvailableStock($product->id, $godownId);
                            if ($availableStock < $request->quantity[$i]) {
                                throw new \Exception("Not enough godown stock for product: {$product->name}");
                            }
                        } else {
                            if ($request->quantity[$i] > $product->current_stock) {
                                throw new \Exception("Not enough stock available for product: {$product->name}");
                            }
                        }
                    }
                    
                    // Create delivery item
                    $item = OtherDeliveryItem::create([
                        'other_delivery_id' => $delivery->id,
                        'product_id' => $productId,
                        'godown_id' => $godownId,
                        'description' => $request->description[$i] ?? null,
                        'quantity' => $request->quantity[$i],
                        'cartons' => $request->cartons[$i] ?? null,
                        'pieces' => $request->pieces[$i] ?? null,
                    ]);
                    
                    \Log::info('Delivery item created with ID:', ['item_id' => $item->id]);
                    
                    // Update product stock
                    $oldStock = $product->current_stock;
                    $product->current_stock -= $request->quantity[$i];
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, -$request->quantity[$i]);
                    }
                    
                    \Log::info('Stock updated for product:', [
                        'product_name' => $product->name,
                        'old_stock' => $oldStock,
                        'new_stock' => $product->current_stock
                    ]);
                    
                    $itemCount++;
                }
            }
            
            \Log::info('Total items created:', ['count' => $itemCount]);
            
            DB::commit();
            \Log::info('Transaction committed successfully');
            
            // Check if "Create and New" was clicked
            if ($request->has('create_and_new') && $request->create_and_new) {
                return redirect()->route('other-deliveries.create')
                    ->with('success', 'Delivery challan created successfully! Ready to create another one.')
                    ->with('auto_focus', true);
            }
            
            return redirect()->route('other-deliveries.show', $delivery)
                ->with('success', 'Delivery challan created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Store method failed:', ['message' => $e->getMessage()]);
            \Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);
            
            return redirect()->back()
                ->with('error', 'Delivery challan creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(OtherDelivery $otherDelivery)
    {
        $otherDelivery->load(['items.product.category']);
        return view('other-deliveries.show', compact('otherDelivery'));
    }

    public function edit(OtherDelivery $otherDelivery)
    {
        if ($otherDelivery->status === 'delivered') {
            return redirect()->route('other-deliveries.index')
                ->with('error', 'Delivered challans cannot be edited.');
        }
        
        $otherDelivery->load(['items.product.category']);
        $products = Product::orderBy('name')->get();
        
        return view('other-deliveries.edit', compact('otherDelivery', 'products'));
    }

    public function update(Request $request, OtherDelivery $otherDelivery)
    {
        if ($otherDelivery->status === 'delivered') {
            return redirect()->route('other-deliveries.index')
                ->with('error', 'Delivered challans cannot be edited.');
        }
        
        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'required|string',
            'recipient_phone' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:20',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_transit,delivered,cancelled',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'description' => 'nullable|array',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'cartons' => 'nullable|array',
            'pieces' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $existingItems = OtherDeliveryItem::withoutGlobalScopes()
                ->where('other_delivery_id', $otherDelivery->id)
                ->get();

            // First, restore the stock for all current items
            foreach ($existingItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock += $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, $item->quantity);
                    }
                }
            }
            
            // Update delivery
            $otherDelivery->update([
                'delivery_date' => $request->delivery_date,
                'recipient_name' => $request->recipient_name,
                'recipient_address' => $request->recipient_address,
                'recipient_phone' => $request->recipient_phone,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);
            
            // Delete existing items
            OtherDeliveryItem::withoutGlobalScopes()->where('other_delivery_id', $otherDelivery->id)->delete();
            
            // Create new items and update stock
            for ($i = 0; $i < count($request->product_id); $i++) {
                if (isset($request->quantity[$i]) && $request->quantity[$i] > 0) {
                    $productId = $request->product_id[$i];
                    $product = Product::findOrFail($productId);
                    
                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $godownId = GodownStockService::resolveGodownId(null, $product);
                    }

                    $preventNegative = ErpFeatureSetting::isEnabled('prevent_negative_stock');
                    $isStockManaged = $product->is_stock_managed !== false;
                    if ($preventNegative && $isStockManaged) {
                        if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                            $availableStock = GodownStockService::getAvailableStock($product->id, $godownId);
                            if ($availableStock < $request->quantity[$i]) {
                                throw new \Exception("Not enough godown stock for product: {$product->name}");
                            }
                        } else {
                            if ($request->quantity[$i] > $product->current_stock) {
                                throw new \Exception("Not enough stock available for product: {$product->name}");
                            }
                        }
                    }
                    
                    // Create delivery item
                    OtherDeliveryItem::create([
                        'other_delivery_id' => $otherDelivery->id,
                        'product_id' => $productId,
                        'godown_id' => $godownId,
                        'description' => $request->description[$i],
                        'quantity' => $request->quantity[$i],
                        'cartons' => $request->cartons[$i] ?? null,
                        'pieces' => $request->pieces[$i] ?? null,
                    ]);
                    
                    // Update product stock
                    $product->current_stock -= $request->quantity[$i];
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, -$request->quantity[$i]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('other-deliveries.show', $otherDelivery)
                ->with('success', 'Delivery challan updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Delivery challan update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(OtherDelivery $otherDelivery)
    {
        if ($otherDelivery->status === 'delivered') {
            return redirect()->route('other-deliveries.index')
                ->with('error', 'Delivered challans cannot be deleted.');
        }
        
        DB::beginTransaction();
        try {
            $existingItems = OtherDeliveryItem::withoutGlobalScopes()
                ->where('other_delivery_id', $otherDelivery->id)
                ->get();

            // Restore stock for all items
            foreach ($existingItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock += $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, $item->quantity);
                    }
                }
            }
            
            // Delete delivery and its items (cascade)
            $otherDelivery->delete();
            
            DB::commit();
            
            return redirect()->route('other-deliveries.index')
                ->with('success', 'Delivery challan deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Delivery challan deletion failed: ' . $e->getMessage());
        }
    }
    
    public function print(OtherDelivery $otherDelivery)
    {
        $otherDelivery->load(['items.product.category', 'deliveredBy']);
        return view('other-deliveries.print', compact('otherDelivery'));
    }
    
    // FIXED: Updated updateStatus method to handle DataTables refresh
    public function updateStatus(Request $request, OtherDelivery $otherDelivery)
    {
        $request->validate([
            'status' => 'required|in:pending,in_transit,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $oldStatus = $otherDelivery->status;
        
        $otherDelivery->update([
            'status' => $request->status,
            'status_updated_at' => now(),
            'status_notes' => $request->notes
        ]);
        
        // Log status change if needed
        \Log::info('Status updated', [
            'delivery_id' => $otherDelivery->id,
            'challan' => $otherDelivery->challan_number,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'updated_by' => auth()->id()
        ]);
        
        // Load the updated delivery with relationships for consistent response
        $otherDelivery->load(['deliveredBy']);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'challan' => $otherDelivery->challan_number,
            'status_badge' => $this->getStatusBadge($request->status),
            'action_buttons' => $this->getActionButtons($otherDelivery)
        ]);
    }

    // Bulk status update
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'delivery_ids' => 'required|array',
            'delivery_ids.*' => 'exists:other_deliveries,id',
            'status' => 'required|in:pending,in_transit,delivered,cancelled',
        ]);

        try {
            OtherDelivery::whereIn('id', $request->delivery_ids)
                        ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully for selected deliveries.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Add recipient history method
    public function getRecipientHistory($name)
    {
        $deliveries = OtherDelivery::with(['items.product'])
                                  ->where('recipient_name', $name)
                                  ->latest()
                                  ->limit(10) // Limit to recent 10 deliveries
                                  ->get();
        
        return response()->json($deliveries);
    }

    // Add recipient history view
    public function recipientHistory($name)
    {
        $recipient = OtherDelivery::where('recipient_name', $name)->first();
        if (!$recipient) {
            abort(404, 'Recipient not found');
        }
        
        $deliveries = OtherDelivery::with(['items.product', 'deliveredBy'])
                                  ->where('recipient_name', $name)
                                  ->latest()
                                  ->get();
        
        return view('other-deliveries.recipient-history', compact('deliveries', 'recipient'));
    }
}
