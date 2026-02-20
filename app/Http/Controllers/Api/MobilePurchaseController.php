<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ErpFeatureSetting;
use App\Models\Godown;
use App\Models\Payee;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\Accounting\AutoPostingService;
use App\Services\GodownStockService;
use App\Services\PayeeAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobilePurchaseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $this->tenantIdForUser($user);
        if (!$this->canCreatePurchase($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create purchases.',
            ], 403);
        }

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

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $company = Company::query()
                ->whereKey((int) $validated['company_id'])
                ->when(
                    !empty($tenantId),
                    fn ($q) => $q->where('tenant_id', $tenantId)
                )
                ->first();

            if (!$company) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier company not found for current tenant.',
                ], 422);
            }

            $validItems = array_values(array_filter($validated['items'], function ($item) {
                return !empty($item['product_id']) &&
                    !empty($item['quantity']) &&
                    !empty($item['purchase_price']);
            }));

            if (empty($validItems)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid purchase items were provided.',
                ], 422);
            }

            $labourCost = (float) ($validated['labour_cost'] ?? 0);
            $transportationCost = (float) ($validated['transportation_cost'] ?? 0);
            $otherCost = (float) ($validated['other_cost'] ?? 0);
            $totalAdditionalCost = $labourCost + $transportationCost + $otherCost;

            $godownId = null;
            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $requestedGodownId = isset($validated['godown_id']) ? (int) $validated['godown_id'] : null;
                $godownId = GodownStockService::resolveGodownId($requestedGodownId);
                if ($godownId) {
                    $godown = Godown::query()
                        ->whereKey($godownId)
                        ->when(
                            !empty($tenantId),
                            fn ($q) => $q->where('tenant_id', $tenantId)
                        )
                        ->first();
                    if (!$godown) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected godown is not available for current tenant.',
                        ], 422);
                    }
                }
            }

            $purchase = Purchase::create([
                'purchase_date' => $validated['purchase_date'],
                'invoice_no' => $validated['invoice_no'] ?? null,
                'company_id' => $company->id,
                'godown_id' => $godownId,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => 0,
                'labour_cost' => $labourCost,
                'transportation_cost' => $transportationCost,
                'other_cost' => $otherCost,
                'other_cost_description' => $validated['other_cost_description'] ?? null,
                'cost_distribution_method' => $validated['cost_distribution_method'] ?? 'per_value',
                'update_product_prices' => (bool) ($validated['update_product_prices'] ?? false),
                'grand_total' => 0,
            ]);

            $totalAmount = 0;
            $totalQuantity = 0;
            foreach ($validItems as $item) {
                $totalAmount += ((float) $item['quantity']) * ((float) $item['purchase_price']);
                $totalQuantity += (float) $item['quantity'];
            }

            foreach ($validItems as $item) {
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
                $purchasePrice = (float) $item['purchase_price'];
                $totalPrice = $quantity * $purchasePrice;

                $additionalCost = 0;
                $effectivePrice = $purchasePrice;

                if ($totalAdditionalCost > 0) {
                    $method = $validated['cost_distribution_method'] ?? 'per_value';
                    switch ($method) {
                        case 'per_quantity':
                            $additionalCost = ($totalQuantity > 0) ? ($totalAdditionalCost / $totalQuantity) * $quantity : 0;
                            $effectivePrice = ($totalQuantity > 0)
                                ? $purchasePrice + ($totalAdditionalCost / $totalQuantity)
                                : $purchasePrice;
                            break;
                        case 'equal':
                            $additionalCost = $totalAdditionalCost / count($validItems);
                            $effectivePrice = $purchasePrice + ($additionalCost / $quantity);
                            break;
                        case 'per_value':
                        default:
                            $proportion = ($totalAmount > 0) ? ($totalPrice / $totalAmount) : 0;
                            $additionalCost = $totalAdditionalCost * $proportion;
                            $effectivePrice = $purchasePrice + ($additionalCost / $quantity);
                            break;
                    }
                }

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'godown_id' => $godownId,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'total_price' => $totalPrice,
                    'additional_cost' => $additionalCost,
                    'effective_price' => $effectivePrice,
                ]);

                $product->current_stock += $quantity;
                if (($validated['update_product_prices'] ?? false) && $totalAdditionalCost > 0) {
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

            $purchase->total_amount = $totalAmount;
            $purchase->grand_total = $totalAmount + $totalAdditionalCost;
            $purchase->save();

            $this->syncSupplierPayee($company);
            app(AutoPostingService::class)->postPurchase($purchase);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase created successfully.',
                'data' => [
                    'id' => $purchase->id,
                    'purchase_date' => optional($purchase->purchase_date)->toDateString(),
                    'invoice_no' => $purchase->invoice_no,
                    'grand_total' => (float) $purchase->grand_total,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Purchase creation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function canCreatePurchase($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        return method_exists($user, 'can') && $user->can('purchase-create');
    }

    protected function syncSupplierPayee(Company $company): void
    {
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

    protected function tenantIdForUser($user): ?int
    {
        if (!$user) {
            return null;
        }

        $tokenName = (string) ($user->currentAccessToken()?->name ?? '');
        if (preg_match('/\|tenant:(\d+)$/', $tokenName, $matches)) {
            return (int) $matches[1];
        }

        if (!empty($user->tenant_id)) {
            return (int) $user->tenant_id;
        }

        return 1;
    }
}
