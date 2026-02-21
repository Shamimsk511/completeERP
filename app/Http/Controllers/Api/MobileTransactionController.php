<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Services\PaymentAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileTransactionController extends Controller
{
    public function __construct(protected PaymentAllocationService $paymentAllocationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessTransactions($user, false)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view transactions.',
            ], 403);
        }

        $query = Transaction::query()
            ->with(['customer:id,name,phone', 'invoice:id,invoice_number'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $tenantId = $this->tenantIdForUser($user);
        if ($tenantId) {
            $query->where('transactions.tenant_id', $tenantId);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', (string) $request->type);
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $transactions->getCollection()->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'created_at' => optional($transaction->created_at)->toISOString(),
                    'type' => $transaction->type,
                    'purpose' => $transaction->purpose,
                    'method' => $transaction->method,
                    'amount' => (float) $transaction->amount,
                    'discount_amount' => (float) ($transaction->discount_amount ?? 0),
                    'reference' => $transaction->reference,
                    'customer' => [
                        'id' => $transaction->customer?->id,
                        'name' => $transaction->customer?->name,
                        'phone' => $transaction->customer?->phone,
                    ],
                    'invoice' => [
                        'id' => $transaction->invoice?->id,
                        'invoice_number' => $transaction->invoice?->invoice_number,
                    ],
                ])->values(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessTransactions($user, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create transactions.',
            ], 403);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank,mobile_bank,cheque',
            'account_id' => 'nullable|exists:accounts,id',
            'purpose' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        $tenantId = $this->tenantIdForUser($user);

        $customer = Customer::query()
            ->whereKey((int) $validated['customer_id'])
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found for current tenant.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'invoice_id' => $validated['invoice_id'] ?? null,
                'type' => 'debit',
                'purpose' => $validated['purpose'] ?? 'Customer payment',
                'method' => $validated['method'],
                'account_id' => $validated['account_id'] ?? null,
                'amount' => (float) $validated['amount'],
                'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
                'discount_reason' => $validated['discount_reason'] ?? null,
                'note' => $validated['note'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'tenant_id' => $tenantId,
            ]);

            $this->paymentAllocationService->allocatePayments($customer->id);
            $customer->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment transaction created successfully.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'customer_id' => $customer->id,
                    'outstanding_balance' => (float) ($customer->outstanding_balance ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment transaction: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function canAccessTransactions($user, bool $forCreate): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        if (!method_exists($user, 'can')) {
            return false;
        }

        if ($forCreate) {
            return $user->can('transaction-create');
        }

        return $user->can('transaction-list') || $user->can('transaction-create');
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

