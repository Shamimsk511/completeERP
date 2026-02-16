<?php

namespace App\Http\Controllers;

use App\Models\OtherDeliveryReturn;
use App\Models\OtherDeliveryReturnItem;
use App\Models\Product;
use App\Models\ErpFeatureSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\GodownStockService;

class OtherDeliveryReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:other-delivery-return-list|other-delivery-return-create|other-delivery-return-edit|other-delivery-return-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:other-delivery-return-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:other-delivery-return-edit', ['only' => ['edit', 'update', 'updateStatus']]);
        $this->middleware('permission:other-delivery-return-delete', ['only' => ['destroy']]);
        $this->middleware('permission:other-delivery-return-print', ['only' => ['print']]);
    }

    public function index()
    {
        $returns = OtherDeliveryReturn::with(['receivedBy'])->latest()->get();
        return view('other-delivery-returns.index', compact('returns'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $return_number = OtherDeliveryReturn::getNextReturnNumber();
        $return_date = Carbon::now()->format('Y-m-d');
        
        return view('other-delivery-returns.create', compact('products', 'return_number', 'return_date'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_number' => ['required', 'string', $this->tenantUniqueRule('other_delivery_returns', 'return_number')],
            'return_date' => 'required|date',
            'returner_name' => 'required|string|max:255',
            'returner_address' => 'required|string',
            'returner_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'description' => 'nullable|array',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'cartons' => 'nullable|array',
            'pieces' => 'nullable|array',
        ]);
\Log::info('Return form submitted', $request->all());
        DB::beginTransaction();
        try {
            // Create return record
            $return = OtherDeliveryReturn::create([
                'return_number' => $request->return_number,
                'return_date' => $request->return_date,
                'returner_name' => $request->returner_name,
                'returner_address' => $request->returner_address,
                'returner_phone' => $request->returner_phone,
                'notes' => $request->notes,
                'status' => 'pending',
                'received_by' => Auth::id(),
            ]);
            
            // Create return items and update stock
            for ($i = 0; $i < count($request->product_id); $i++) {
                if (isset($request->quantity[$i]) && $request->quantity[$i] > 0) {
                    $productId = $request->product_id[$i];
                    $product = Product::findOrFail($productId);

                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $godownId = GodownStockService::resolveGodownId(null, $product);
                    }
                    
                    // Create return item
                    OtherDeliveryReturnItem::create([
                        'other_delivery_return_id' => $return->id,
                        'product_id' => $productId,
                        'godown_id' => $godownId,
                        'description' => $request->description[$i],
                        'quantity' => $request->quantity[$i],
                        'cartons' => $request->cartons[$i] ?? null,
                        'pieces' => $request->pieces[$i] ?? null,
                    ]);
                    
                    // Update product stock (add back to inventory)
                    $product->current_stock += $request->quantity[$i];
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, $request->quantity[$i]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('other-delivery-returns.show', $return)
                ->with('success', 'Return record created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Return record creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(OtherDeliveryReturn $otherDeliveryReturn)
    {
        $otherDeliveryReturn->load(['items.product.category', 'receivedBy']);
        return view('other-delivery-returns.show', compact('otherDeliveryReturn'));
    }

    public function edit(OtherDeliveryReturn $otherDeliveryReturn)
    {
        if ($otherDeliveryReturn->status === 'completed') {
            return redirect()->route('other-delivery-returns.index')
                ->with('error', 'Completed returns cannot be edited.');
        }
        
        $otherDeliveryReturn->load(['items.product.category']);
        $products = Product::orderBy('name')->get();
        
        return view('other-delivery-returns.edit', compact('otherDeliveryReturn', 'products'));
    }

    public function update(Request $request, OtherDeliveryReturn $otherDeliveryReturn)
    {
        if ($otherDeliveryReturn->status === 'completed') {
            return redirect()->route('other-delivery-returns.index')
                ->with('error', 'Completed returns cannot be edited.');
        }
        
        $validated = $request->validate([
            'return_date' => 'required|date',
            'returner_name' => 'required|string|max:255',
            'returner_address' => 'required|string',
            'returner_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,rejected',
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
            $existingItems = OtherDeliveryReturnItem::withoutGlobalScopes()
                ->where('other_delivery_return_id', $otherDeliveryReturn->id)
                ->get();

            // First, reverse the stock adjustments for all current items
            foreach ($existingItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock -= $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, -$item->quantity);
                    }
                }
            }
            
            // Update return record
            $otherDeliveryReturn->update([
                'return_date' => $request->return_date,
                'returner_name' => $request->returner_name,
                'returner_address' => $request->returner_address,
                'returner_phone' => $request->returner_phone,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);
            
            // Delete existing items
            OtherDeliveryReturnItem::withoutGlobalScopes()->where('other_delivery_return_id', $otherDeliveryReturn->id)->delete();
            
            // Create new items and update stock
            for ($i = 0; $i < count($request->product_id); $i++) {
                if (isset($request->quantity[$i]) && $request->quantity[$i] > 0) {
                    $productId = $request->product_id[$i];
                    $product = Product::findOrFail($productId);

                    $godownId = null;
                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $godownId = GodownStockService::resolveGodownId(null, $product);
                    }
                    
                    // Create return item
                    OtherDeliveryReturnItem::create([
                        'other_delivery_return_id' => $otherDeliveryReturn->id,
                        'product_id' => $productId,
                        'godown_id' => $godownId,
                        'description' => $request->description[$i],
                        'quantity' => $request->quantity[$i],
                        'cartons' => $request->cartons[$i] ?? null,
                        'pieces' => $request->pieces[$i] ?? null,
                    ]);
                    
                    // Update product stock (add back to inventory)
                    $product->current_stock += $request->quantity[$i];
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                        GodownStockService::adjustStock($product->id, $godownId, $request->quantity[$i]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('other-delivery-returns.show', $otherDeliveryReturn)
                ->with('success', 'Return record updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Return record update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(OtherDeliveryReturn $otherDeliveryReturn)
    {
        if ($otherDeliveryReturn->status === 'completed') {
            return redirect()->route('other-delivery-returns.index')
                ->with('error', 'Completed returns cannot be deleted.');
        }
        
        DB::beginTransaction();
        try {
            $existingItems = OtherDeliveryReturnItem::withoutGlobalScopes()
                ->where('other_delivery_return_id', $otherDeliveryReturn->id)
                ->get();

            // Reverse stock adjustments for all items
            foreach ($existingItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock -= $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, -$item->quantity);
                    }
                }
            }
            
            // Delete return and its items (cascade)
            $otherDeliveryReturn->delete();
            
            DB::commit();
            
            return redirect()->route('other-delivery-returns.index')
                ->with('success', 'Return record deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Return record deletion failed: ' . $e->getMessage());
        }
    }
    
    public function print(OtherDeliveryReturn $otherDeliveryReturn)
    {
        $otherDeliveryReturn->load(['items.product.category', 'receivedBy']);
        return view('other-delivery-returns.print', compact('otherDeliveryReturn'));
    }
    
   public function updateStatus(Request $request, OtherDeliveryReturn $otherDeliveryReturn)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,completed,rejected',
    ]);
    
    $oldStatus = $otherDeliveryReturn->status;
    $newStatus = $request->status;
    
    DB::beginTransaction();
    try {
        $existingItems = OtherDeliveryReturnItem::withoutGlobalScopes()
            ->where('other_delivery_return_id', $otherDeliveryReturn->id)
            ->get();

        // Update the status
        $otherDeliveryReturn->update([
            'status' => $newStatus
        ]);
        
        // If status is changing to completed, ensure stock is updated
            if ($oldStatus != 'completed' && $newStatus == 'completed') {
                // Update product stock for each item
                foreach ($existingItems as $item) {
                    $product = Product::findOrFail($item->product_id);
                    
                    // Check if we have enough stock
                    if ($product->current_stock >= $item->quantity) {
                        $product->current_stock += $item->quantity; // Add to inventory for returns
                        $product->save();

                        if (ErpFeatureSetting::isEnabled('godown_management')) {
                            $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                            if ($resolvedGodownId) {
                                GodownStockService::adjustStock($product->id, $resolvedGodownId, $item->quantity);
                            }
                        }
                    } else {
                        throw new \Exception("Not enough stock for product: {$product->name}");
                    }
                }
            }
        
        // If status is changing from completed to something else, reverse stock updates
            if ($oldStatus == 'completed' && $newStatus != 'completed') {
                // Reverse product stock for each item
                foreach ($existingItems as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->current_stock -= $item->quantity; // Remove from inventory
                    $product->save();

                    if (ErpFeatureSetting::isEnabled('godown_management')) {
                        $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                        if ($resolvedGodownId) {
                            GodownStockService::adjustStock($product->id, $resolvedGodownId, -$item->quantity);
                        }
                    }
                }
            }
        
        DB::commit();
        
        return redirect()->route('other-delivery-returns.show', $otherDeliveryReturn)
            ->with('success', 'Return status updated successfully.');
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()
            ->with('error', 'Status update failed: ' . $e->getMessage());
    }
}
}
