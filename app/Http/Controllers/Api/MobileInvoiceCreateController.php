<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PaymentAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileInvoiceCreateController extends Controller
{
    public function __construct(protected PaymentAllocationService $paymentAllocationService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canCreateInvoice($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create invoices.',
            ], 403);
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,bank,mobile_bank,cheque',
            'account_id' => 'nullable|exists:accounts,id',
            'delivery_status' => 'nullable|in:pending,partial,delivered',
            'invoice_type' => 'nullable|in:tiles,other',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.boxes' => 'nullable|numeric|min:0',
            'items.*.pieces' => 'nullable|numeric|min:0',
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

        $deliveryStatus = (string) ($validated['delivery_status'] ?? 'pending');
        $invoiceType = (string) ($validated['invoice_type'] ?? 'tiles');
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);

        DB::beginTransaction();
        try {
            $subtotal = 0.0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::query()
                    ->whereKey((int) $item['product_id'])
                    ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                    ->first();

                if (!$product) {
                    throw new \RuntimeException('One or more products are not available for current tenant.');
                }

                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $lineTotal = round($quantity * $unitPrice, 2);

                if ($deliveryStatus === 'delivered' && $product->is_stock_managed !== false) {
                    if ((float) $product->current_stock < $quantity) {
                        throw new \RuntimeException('Not enough stock for product: ' . $product->name);
                    }
                }

                $itemsData[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'description' => $item['description'] ?? $product->name,
                    'boxes' => $item['boxes'] ?? null,
                    'pieces' => $item['pieces'] ?? null,
                ];

                $subtotal += $lineTotal;
            }

            $total = max(0, round($subtotal - $discount, 2));
            $paidAmount = min($paidAmount, $total);

            $invoice = Invoice::create([
                'invoice_number' => $this->generateUniqueInvoiceNumber(),
                'customer_id' => $customer->id,
                'invoice_date' => $validated['invoice_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'paid_amount' => 0,
                'due_amount' => $total,
                'previous_balance' => (float) ($customer->outstanding_balance ?? 0),
                'initial_paid_amount' => $paidAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'due',
                'notes' => $validated['notes'] ?? null,
                'invoice_type' => $invoiceType,
                'delivery_status' => $deliveryStatus,
                'tenant_id' => $tenantId,
            ]);

            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['line_total'],
                    'boxes' => $item['boxes'],
                    'pieces' => $item['pieces'],
                    'tenant_id' => $tenantId,
                ]);

                if ($deliveryStatus === 'delivered') {
                    $product = $item['product'];
                    $product->current_stock = (float) $product->current_stock - $item['quantity'];
                    $product->save();
                }
            }

            Transaction::create([
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'type' => 'credit',
                'purpose' => 'Invoice #' . $invoice->invoice_number,
                'method' => $validated['payment_method'],
                'amount' => $total,
                'discount_amount' => $discount,
                'discount_reason' => $discount > 0 ? 'Invoice discount' : null,
                'note' => 'Invoice sale',
                'reference' => $invoice->invoice_number,
                'tenant_id' => $tenantId,
            ]);

            if ($paidAmount > 0) {
                Transaction::create([
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'type' => 'debit',
                    'purpose' => 'Payment for Invoice #' . $invoice->invoice_number,
                    'method' => $validated['payment_method'],
                    'account_id' => $validated['account_id'] ?? null,
                    'amount' => $paidAmount,
                    'discount_amount' => 0,
                    'note' => 'Invoice payment',
                    'reference' => $invoice->invoice_number,
                    'tenant_id' => $tenantId,
                ]);
            }

            $this->paymentAllocationService->allocatePayments($customer->id);
            $invoice->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total' => (float) $invoice->total,
                    'paid_amount' => (float) $invoice->paid_amount,
                    'due_amount' => (float) $invoice->due_amount,
                    'payment_status' => $invoice->payment_status,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function canCreateInvoice($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        return method_exists($user, 'can') && $user->can('invoice-create');
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

    protected function generateUniqueInvoiceNumber(): string
    {
        do {
            $lastInvoiceNumber = Invoice::withTrashed()->max('invoice_number');
            $nextNumber = $lastInvoiceNumber ? ((int) substr($lastInvoiceNumber, 4)) + 1 : 1;
            $invoiceNumber = 'INV-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
        } while (Invoice::withTrashed()->where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}

