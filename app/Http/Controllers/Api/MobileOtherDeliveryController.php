<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ErpFeatureSetting;
use App\Models\Godown;
use App\Models\OtherDelivery;
use App\Models\OtherDeliveryItem;
use App\Models\Product;
use App\Services\GodownStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileOtherDeliveryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $this->tenantIdForUser($user);
        if (!$this->canCreateOtherDelivery($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create other deliveries.',
            ], 403);
        }

        $validated = $request->validate([
            'challan_number' => 'nullable|string|max:255',
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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cartons' => 'nullable|numeric|min:0',
            'items.*.pieces' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $challanNumber = trim((string) ($validated['challan_number'] ?? ''));
            if ($challanNumber === '') {
                $challanNumber = OtherDelivery::getNextChallanNumber();
            }

            $existingChallan = OtherDelivery::query()
                ->where('challan_number', $challanNumber)
                ->when(
                    !empty($tenantId),
                    fn ($q) => $q->where('tenant_id', $tenantId)
                )
                ->exists();

            if ($existingChallan) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Challan number already exists.',
                ], 422);
            }

            $delivery = OtherDelivery::create([
                'challan_number' => $challanNumber,
                'delivery_date' => $validated['delivery_date'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_address' => $validated['recipient_address'],
                'recipient_phone' => $validated['recipient_phone'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'driver_phone' => $validated['driver_phone'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['delivery_status'],
                'delivered_by' => $user->id,
            ]);

            $preventNegative = ErpFeatureSetting::isEnabled('prevent_negative_stock');
            $godownEnabled = ErpFeatureSetting::isEnabled('godown_management');

            foreach ($validated['items'] as $item) {
                $product = Product::query()
                    ->whereKey((int) $item['product_id'])
                    ->when(
                        !empty($tenantId),
                        fn ($q) => $q->where('tenant_id', $tenantId)
                    )
                    ->first();

                if (!$product) {
                    throw new \RuntimeException('One or more products are not available for current tenant.');
                }

                $quantity = (float) $item['quantity'];
                $godownId = null;
                if ($godownEnabled) {
                    $godownId = GodownStockService::resolveGodownId(null, $product);
                    if ($godownId) {
                        $godown = Godown::query()
                            ->whereKey($godownId)
                            ->when(
                                !empty($tenantId),
                                fn ($q) => $q->where('tenant_id', $tenantId)
                            )
                            ->first();
                        if (!$godown) {
                            throw new \RuntimeException('Product godown is not available for current tenant.');
                        }
                    }
                }

                $isStockManaged = $product->is_stock_managed !== false;
                if ($preventNegative && $isStockManaged) {
                    if ($godownEnabled && $godownId) {
                        $availableStock = GodownStockService::getAvailableStock($product->id, $godownId);
                        if ($availableStock < $quantity) {
                            throw new \RuntimeException('Not enough godown stock for product: ' . $product->name);
                        }
                    } elseif ($product->current_stock < $quantity) {
                        throw new \RuntimeException('Not enough stock for product: ' . $product->name);
                    }
                }

                OtherDeliveryItem::create([
                    'other_delivery_id' => $delivery->id,
                    'product_id' => $product->id,
                    'godown_id' => $godownId,
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'cartons' => $item['cartons'] ?? null,
                    'pieces' => $item['pieces'] ?? null,
                ]);

                $product->current_stock -= $quantity;
                $product->save();

                if ($godownEnabled && $godownId) {
                    GodownStockService::adjustStock($product->id, $godownId, -$quantity);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Other delivery created successfully.',
                'data' => [
                    'id' => $delivery->id,
                    'challan_number' => $delivery->challan_number,
                    'delivery_date' => optional($delivery->delivery_date)->toDateString(),
                    'status' => $delivery->status,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Other delivery creation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function canCreateOtherDelivery($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        return method_exists($user, 'can') && $user->can('other-delivery-create');
    }

    protected function tenantIdForUser($user): ?int
    {
        if (!$user) {
            return null;
        }

        $tokenName = (string) ($user->currentAccessToken()?->name ?? '');
        if (preg_match('/\|tenant:(\d+)$/', $tokenName, $matches)) {
            return (int) $matches[1];
        }

        return !empty($user->tenant_id) ? (int) $user->tenant_id : null;
    }
}
