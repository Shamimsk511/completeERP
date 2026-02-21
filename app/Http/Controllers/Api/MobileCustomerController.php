<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessCustomers($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view customers.',
            ], 403);
        }

        $query = Customer::query()
            ->select(['id', 'name', 'phone', 'address', 'outstanding_balance'])
            ->orderBy('name');

        $tenantId = $this->tenantIdForUser($user);
        if ($tenantId) {
            $query->where('customers.tenant_id', $tenantId);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->input('per_page', 30);
        $perPage = max(1, min(100, $perPage));

        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $customers->getCollection()->map(fn (Customer $customer) => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'outstanding_balance' => (float) $customer->outstanding_balance,
                ])->values(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ],
            ],
        ]);
    }

    public function openInvoices(Request $request, Customer $customer): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessCustomers($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view customers.',
            ], 403);
        }

        $tenantId = $this->tenantIdForUser($user);
        if ($tenantId && (int) $customer->tenant_id !== $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $invoices = $customer->invoices()
            ->select(['id', 'invoice_number', 'invoice_date', 'total', 'paid_amount', 'due_amount', 'payment_status'])
            ->where('due_amount', '>', 0)
            ->orderByDesc('invoice_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => optional($invoice->invoice_date)->toDateString(),
                'total' => (float) $invoice->total,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount' => (float) $invoice->due_amount,
                'payment_status' => $invoice->payment_status,
            ])->values(),
        ]);
    }

    protected function canAccessCustomers($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        return method_exists($user, 'can') && (
            $user->can('customer-list') ||
            $user->can('transaction-create') ||
            $user->can('invoice-create')
        );
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

