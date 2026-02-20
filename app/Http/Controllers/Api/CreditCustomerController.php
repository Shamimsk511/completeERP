<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$this->canReadCustomers($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view customers.',
            ], 403);
        }

        $query = Customer::query()
            ->select([
                'id',
                'name',
                'phone',
                'address',
                'outstanding_balance',
                'tenant_id',
            ])
            ->where('outstanding_balance', '>', 0);

        if (!$this->applyTenantFilter($query, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant is assigned to this user.',
            ], 422);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $customers = $query
            ->orderByDesc('outstanding_balance')
            ->orderBy('name')
            ->paginate($perPage);

        $items = $customers->getCollection()->map(function (Customer $customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'outstanding_balance' => (float) $customer->outstanding_balance,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ],
            ],
        ]);
    }

    protected function canReadCustomers($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        if (method_exists($user, 'can') && ($user->can('customer-list') || $user->can('invoice-list'))) {
            return true;
        }

        return false;
    }

    protected function applyTenantFilter($query, $user): bool
    {
        if (!property_exists($user, 'tenant_id')) {
            return false;
        }

        $tenantId = $this->tenantIdForUser($user);
        if ($tenantId) {
            $query->where('customers.tenant_id', $tenantId);
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return true;
        }

        return false;
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
