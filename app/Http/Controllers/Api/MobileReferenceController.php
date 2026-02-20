<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\TilesCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileReferenceController extends Controller
{
    public function suppliers(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessMobileOps($user)) {
            return $this->forbidden();
        }

        $query = Company::query()
            ->where(function ($q) {
                $q->whereIn('type', ['supplier', 'both'])
                    ->orWhereNull('type');
            })
            ->orderBy('name');
        if (!$this->applyTenantFilter($query, $user, 'companies')) {
            return $this->noTenant();
        }

        $items = $query->get(['id', 'name', 'contact'])->map(function (Company $company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'contact' => $company->contact,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessMobileOps($user)) {
            return $this->forbidden();
        }

        $query = Product::query()
            ->with([
                'company:id,name',
                'category:id,name,box_pcs,pieces_feet',
            ])
            ->orderBy('name');

        if (!$this->applyTenantFilter($query, $user, 'products')) {
            return $this->noTenant();
        }

        $inStock = filter_var($request->input('in_stock', false), FILTER_VALIDATE_BOOLEAN);
        if ($inStock) {
            $query->where('current_stock', '>', 0);
        }

        $items = $query->get([
            'id',
            'name',
            'company_id',
            'category_id',
            'current_stock',
            'purchase_price',
            'sale_price',
            'is_stock_managed',
        ])->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'current_stock' => (float) $product->current_stock,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
                'is_stock_managed' => $product->is_stock_managed !== false,
                'company' => [
                    'id' => $product->company?->id,
                    'name' => $product->company?->name,
                ],
                'category' => [
                    'id' => $product->category?->id,
                    'name' => $product->category?->name,
                    'box_pcs' => $product->category?->box_pcs,
                    'pieces_feet' => $product->category?->pieces_feet,
                ],
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function decorCategories(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessMobileOps($user)) {
            return $this->forbidden();
        }

        $query = TilesCategory::query()
            ->with('calculationSettings')
            ->orderBy('name');

        if (!$this->applyTenantFilter($query, $user, 'tiles_categories')) {
            return $this->noTenant();
        }

        $items = $query->get(['id', 'name', 'height', 'width'])->map(function (TilesCategory $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'height' => (float) ($category->height ?? 0),
                'width' => (float) ($category->width ?? 0),
                'defaults' => [
                    'light_times' => (float) ($category->calculationSettings?->light_times ?? 1),
                    'deco_times' => (float) ($category->calculationSettings?->deco_times ?? 1),
                    'deep_times' => (float) ($category->calculationSettings?->deep_times ?? 1),
                ],
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    protected function canAccessMobileOps($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        if (method_exists($user, 'can') && (
            $user->can('purchase-create') ||
            $user->can('other-delivery-create') ||
            $user->can('invoice-create')
        )) {
            return true;
        }

        return false;
    }

    protected function applyTenantFilter($query, $user, string $table): bool
    {
        if (!property_exists($user, 'tenant_id')) {
            return false;
        }

        $tenantId = $this->tenantIdForUser($user);
        if ($tenantId) {
            $query->where($table . '.tenant_id', $tenantId);
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return true;
        }

        return false;
    }

    protected function forbidden(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to access this module.',
        ], 403);
    }

    protected function noTenant(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'No tenant is assigned to this user.',
        ], 422);
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
