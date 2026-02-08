<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Challan;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Referrer;
use App\Models\ChallanItem;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use App\Exports\InvoicesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\PaymentAllocationService;
use App\Models\ProductReturnItem;
use App\Support\TenantContext;
use App\Services\TransactionSmsService;
use App\Services\Accounting\AutoPostingService;
use App\Http\Controllers\Concerns\PreventsDuplicateSubmissions;

class InvoiceController extends Controller
{
    use PreventsDuplicateSubmissions;

    protected $paymentAllocationService;
    protected $smsService;

    public function __construct(PaymentAllocationService $paymentAllocationService, TransactionSmsService $smsService)
    {
        $this->paymentAllocationService = $paymentAllocationService;
        $this->smsService = $smsService;
        $this->middleware('permission:invoice-list|invoice-create|invoice-edit|invoice-delete', ['only' => ['index', 'show', 'dataTable', 'getSummary']]);
        $this->middleware('permission:invoice-create', ['only' => ['create', 'store', 'createOther']]);
        $this->middleware('permission:invoice-edit', ['only' => ['edit', 'update', 'updateDeliveryStatus', 'markAsPaid', 'updateReferrerCompensation']]);
        $this->middleware('permission:invoice-delete', ['only' => ['destroy']]);
        $this->middleware('permission:invoice-print', ['only' => ['print']]);
        $this->middleware('permission:invoice-export', ['only' => ['export']]);
    }

    public function dataTable(Request $request)
    {
        // Eager load relationships to avoid N+1 queries
        $query = Invoice::query()
            ->select([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.customer_id',
                'invoices.invoice_type',
                'invoices.total',
                'invoices.paid_amount',
                'invoices.due_amount',
                'invoices.payment_status',
                'invoices.delivery_status',
            ])
            ->with('customer:id,name,phone,address,outstanding_balance');

        // Apply filters
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->has('invoice_number') && $request->invoice_number) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->has('delivery_status') && $request->delivery_status) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->has('invoice_type') && $request->invoice_type) {
            $query->where('invoice_type', $request->invoice_type);
        }

        // Handle global search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply ordering
        $columns = [
            0 => 'invoice_number',
            1 => 'invoice_date',
            2 => 'customer_id',
            3 => 'invoice_type',
            4 => 'total',
            5 => 'paid_amount',
            6 => 'due_amount',
            7 => 'payment_status',
            8 => 'delivery_status'
        ];
        if ($order = $request->input('order.0')) {
            $columnIdx = (int) ($order['column'] ?? 0);
            $columnDir = strtolower($order['dir'] ?? 'asc');
            $columnDir = $columnDir === 'desc' ? 'desc' : 'asc';
            if (isset($columns[$columnIdx])) {
                $column = $columns[$columnIdx];
                if ($column === 'customer_id') {
                    $query->join('customers', 'invoices.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $columnDir)
                        ->orderBy('invoices.id', 'desc')
                        ->select('invoices.*');
                } else {
                    $query->orderBy($column, $columnDir)
                        ->orderBy('id', 'desc');
                }
            }
        } else {
            $query->orderBy('invoice_date', 'desc')
                ->orderBy('id', 'desc');
        }

        // Paginate results
        $recordsTotal = Invoice::count();
        $recordsFiltered = $query->count();
        $invoices = $query->skip($request->input('start', 0))
            ->take($request->input('length', 15))
            ->get();

        $data = $invoices->map(function ($invoice) {
            $paymentStatus = $invoice->payment_status;

            // // Payment status HTML with overpayment indication
            // $paymentStatusHtml = sprintf(
            //     '<span class="badge badge-%s">%s</span>',
            //     $invoice->payment_status == 'paid' ? 'success' : ($invoice->payment_status == 'partial' ? 'info' : 'warning'),
            //     ucfirst($invoice->payment_status)
            // );
            
            // // Add overpayment indicator if due amount is negative
            // if ($invoice->due_amount < 0) {
            //     $paymentStatusHtml .= sprintf(
            //         '<br><small class="text-success"><i class="fas fa-arrow-up"></i> Overpaid: %s</small>',
            //         number_format(abs($invoice->due_amount), 2)
            //     );
            // }
            
            // if ($invoice->payment_status != 'paid' && $invoice->due_amount > 0) {
            //     $paymentStatusHtml .= sprintf(
            //         '<button class="btn btn-sm btn-success ml-2 mark-paid-btn" data-invoice-id="%d" data-due-amount="%s">Paid</button>',
            //         $invoice->id,
            //         number_format($invoice->due_amount, 2)
            //     );
            // }

            $dueAmount = $invoice->due_amount ?? 0;
            $customerOutstanding = (float) ($invoice->customer->outstanding_balance ?? 0);
            $signedOutstanding = $customerOutstanding;
            $overpaidAmount = $dueAmount < 0 ? abs($dueAmount) : 0;
            $canMarkPaid = $paymentStatus != 'paid' && $dueAmount > 0 && Auth::user()->hasRole('Super Admin');

            return [
                'DT_RowId' => 'invoice-' . $invoice->id,
                'DT_RowData' => ['invoice-id' => $invoice->id],
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('d M, Y'),
                'invoice_url' => route('invoices.show', $invoice),
                'customer_name' => $invoice->customer->name,
                'customer_phone' => $invoice->customer->phone ?: 'No phone',
                'customer_address' => $invoice->customer->address ?: 'No address',
                'customer_url' => route('customers.show', $invoice->customer_id),
                'invoice_type' => $invoice->invoice_type,
                'total' => number_format($invoice->total, 2),
                'paid' => number_format($invoice->paid_amount, 2),
                'due' => number_format(abs($dueAmount), 2),
                'due_is_negative' => $dueAmount < 0,
                'customer_outstanding' => number_format(abs($customerOutstanding), 2),
                'customer_outstanding_is_negative' => $signedOutstanding < 0,
                'payment_status' => $paymentStatus,
                'delivery_status' => $invoice->delivery_status,
                'can_mark_paid' => $canMarkPaid,
                'mark_paid_due' => number_format($dueAmount, 2),
                'overpaid_amount' => $overpaidAmount > 0 ? number_format($overpaidAmount, 2) : null,
                'actions' => null
            ];
        });

        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function index(Request $request)
    {
        $this->setPageData('Invoices', [
            ['title' => 'Dashboard', 'url' => route('dashboard')],
            ['title' => 'Invoices']
        ]);

        // Eager load relationships
        $query = Invoice::with('customer');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Allocate payments for filtered customers
        $customerIds = (clone $query)->pluck('customer_id')->unique();
        if (!$request->ajax()) {
            $customerIds->each(function ($customerId) {
                $this->paymentAllocationService->allocatePayments($customerId);
            });
        }

        $invoices = $query->latest()->paginate(15);

    // Fix: Calculate totals efficiently - create a separate query for aggregates
    $totalsQuery = Invoice::query();
    
    // Apply the same filters to totals query
    if ($request->filled('search')) {
        $search = $request->search;
        $totalsQuery->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', '%' . $search . '%')
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        });
    }
    if ($request->filled('customer_id')) {
        $totalsQuery->where('customer_id', $request->customer_id);
    }
    if ($request->filled('payment_status')) {
        $totalsQuery->where('payment_status', $request->payment_status);
    }
    if ($request->filled('delivery_status')) {
        $totalsQuery->where('delivery_status', $request->delivery_status);
    }
    if ($request->filled('from_date')) {
        $totalsQuery->whereDate('invoice_date', '>=', $request->from_date);
    }
    if ($request->filled('to_date')) {
        $totalsQuery->whereDate('invoice_date', '<=', $request->to_date);
    }

    $totals = $totalsQuery->selectRaw('COUNT(*) as total_invoices, SUM(total) as total_amount, SUM(paid_amount) as total_paid, SUM(due_amount) as total_due')
        ->first();

    $customers = Customer::orderBy('name')->get(['id', 'name']);

    return view('invoices.index', [
        'invoices' => $invoices,
        'customers' => $customers,
        'totalInvoices' => $totals->total_invoices ?? 0,
        'totalAmount' => number_format($totals->total_amount ?? 0, 2),
        'totalPaid' => number_format($totals->total_paid ?? 0, 2),
        'totalDue' => number_format($totals->total_due ?? 0, 2)
    ]);
    }

    public function export()
    {
        return Excel::download(new InvoicesExport, 'invoices-' . date('Y-m-d') . '.xlsx');
    }

    public function getStatistics(Request $request)
    {
        $query = Invoice::query();

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $totals = $query->selectRaw('COUNT(*) as total_invoices, SUM(total) as total_amount, SUM(paid_amount) as total_paid, SUM(due_amount) as total_due')
            ->first();

        return response()->json([
            'totalInvoices' => $totals->total_invoices,
            'totalAmount' => number_format($totals->total_amount, 2),
            'totalPaid' => number_format($totals->total_paid, 2),
            'totalDue' => number_format($totals->total_due, 2)
        ]);
    }

    public function create()
    {
        return $this->createInvoice('tiles');
    }

    public function createOther()
    {
        return $this->createInvoice('other');
    }

    protected function createInvoice($defaultInvoiceType)
    {
        $this->setPageData('Create New Invoice', [
            ['title' => 'Dashboard', 'url' => route('dashboard')],
            ['title' => 'Invoices', 'url' => route('invoices.index')],
            ['title' => 'Create']
        ]);

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $referrers = Referrer::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::with(['category', 'company'])->orderBy('name')->get(['id', 'name', 'sale_price', 'current_stock', 'purchase_price', 'category_id', 'company_id']);
        $companies = Company::brands()->orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'box_pcs', 'pieces_feet', 'weight_value', 'weight_unit']);
        $invoice_date = date('Y-m-d');
        $invoice_number = $this->generateUniqueInvoiceNumber();

        // Get cash/bank accounts for accounting integration
        $cashBankAccounts = AutoPostingService::getCashBankAccounts();

        return view('invoices.create', compact(
            'customers',
            'referrers',
            'products',
            'invoice_date',
            'invoice_number',
            'companies',
            'categories',
            'defaultInvoiceType',
            'cashBankAccounts'
        ));
    }

    protected function resolveDefaultSalesAccountCode(string $invoiceType): string
    {
        return match ($invoiceType) {
            'tiles' => 'SALES-TILES',
            'sanitary' => 'SALES-SANITARY',
            'other', 'paints' => 'SALES-PAINTS',
            default => 'SALES-TILES',
        };
    }

    protected function resolveSalesAccountId(string $invoiceType): ?int
    {
        $code = $this->resolveDefaultSalesAccountCode($invoiceType);

        $account = Account::where('code', $code)->first();
        if (!$account) {
            $account = Account::whereHas('accountGroup', fn($q) => $q->where('code', 'SALES'))->first();
        }

        return $account?->id;
    }

public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'referrer_id' => 'nullable|exists:referrers,id',
            'referrer_compensated' => 'nullable|boolean',
            'payment_method' => 'required',
            'account_id' => 'nullable|exists:accounts,id',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric', // Allow negative values for overpayment
            'delivery_status' => 'required|in:pending,partial,delivered',
            'invoice_type' => 'required|in:tiles,other',
            'notes' => 'nullable|string',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric|min:0.01',
            'unit_price.*' => 'required|numeric|min:0',
            'item_total.*' => 'required|numeric|min:0',
            'boxes.*' => 'nullable|numeric|min:0',
            'pieces.*' => 'nullable|numeric|min:0',
            'description.*' => 'nullable|string',
             'code.*' => 'nullable|string|max:255',
        ]);

        if (!$this->claimIdempotency($request, 'invoice')) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate submission detected. Please wait and try again if needed.'
            ], 409);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($request->customer_id);
            $forceNegativeStock = $request->force_negative_stock == '1';

            // Stock validation - only for delivered orders unless forced
            if (!$forceNegativeStock && $request->delivery_status === 'delivered') {
                $stockIssues = [];
                foreach ($request->product_id as $index => $productId) {
                    $product = Product::findOrFail($productId);
                    $quantity = $request->quantity[$index];
                    if ($product->current_stock < $quantity) {
                        $stockIssues[] = [
                            'name' => $product->name,
                            'available' => $product->current_stock,
                            'required' => $quantity,
                            'shortage' => $quantity - $product->current_stock
                        ];
                    }
                }
                
                if (!empty($stockIssues)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'stock_issues' => $stockIssues
                    ], 422);
                }
            }

            // Capture customer's current outstanding balance BEFORE this invoice (snapshot)
            $previousBalance = $customer->outstanding_balance ?? 0;

            // Create invoice with initial values (will be recalculated by allocation service)
            $referrerId = $request->referrer_id;
            $referrerCompensated = $referrerId ? (bool) $request->referrer_compensated : false;

            $invoice = Invoice::create([
                'invoice_number' => $this->generateUniqueInvoiceNumber(),
                'invoice_date' => $request->invoice_date,
                'customer_id' => $request->customer_id,
                'referrer_id' => $referrerId,
                'referrer_compensated' => $referrerCompensated,
                'payment_method' => $request->payment_method,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid_amount' => 0, // Will be calculated by allocation service
                'due_amount' => $request->total, // Will be calculated by allocation service
                'previous_balance' => $previousBalance, // Snapshot: customer balance BEFORE this invoice
                'initial_paid_amount' => $request->paid_amount, // Snapshot: payment at invoice creation
                'delivery_status' => $request->delivery_status,
                'invoice_type' => $request->invoice_type,
                'sales_account_id' => $this->resolveSalesAccountId($request->invoice_type),
                'notes' => $request->notes,
                'payment_status' => 'due', // Will be calculated by allocation service
            ]);

            // Create invoice items
            $itemTenantId = $invoice->tenant_id ?? TenantContext::currentId();
            $itemsData = [];
            foreach ($request->product_id as $index => $productId) {
                $itemsData[] = [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $itemTenantId,
                    'product_id' => $productId,
                    'quantity' => $request->quantity[$index],
                    'unit_price' => $request->unit_price[$index],
                    'total' => $request->item_total[$index],
                    'boxes' => $request->boxes[$index] ?? null,
                    'pieces' => $request->pieces[$index] ?? null,
                    'description' => $request->description[$index] ?? null,
                    'code' => $request->code[$index] ?? null, 
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            InvoiceItem::insert($itemsData);

            // Update stock if delivered
            if ($request->delivery_status === 'delivered') {
                foreach ($request->product_id as $index => $productId) {
                    $product = Product::findOrFail($productId);
                    $product->current_stock -= $request->quantity[$index];
                    $product->save();
                }
            }

            // Handle transactions
            $this->handleInvoiceTransactions($invoice, $customer, $request);

            // Re-allocate payments to ensure correct balance and payment status
            $this->paymentAllocationService->allocatePayments($customer->id);

            DB::commit();
            Log::info('Invoice created successfully: ' . $invoice->invoice_number);
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice_number' => $invoice->invoice_number,
                'redirect' => route('invoices.show', $invoice->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->releaseIdempotency($request, 'invoice');
            Log::error('Error creating invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

private function calculatePaymentStatus($paidAmount, $total)
    {
        $dueAmount = $total - $paidAmount;
        
        if ($dueAmount <= 0) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partial';
        } else {
            return 'due';
        }
    }

 private function handleInvoiceTransactions($invoice, $customer, $request, $isUpdate = false)
    {
        $paidAmount = $request->paid_amount;
        $total = $request->total; // After-discount total
        $discount = $request->discount ?? 0;

        // Create credit transaction
        $creditTransaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'type' => 'credit',
            'purpose' => 'Invoice #' . $invoice->invoice_number,
            'amount' => $total, // After-discount total
            'method' => $request->payment_method,
            'note' => 'Invoice sale',
            'reference' => $invoice->invoice_number,
            'discount_amount' => $discount, // Store discount for record
            'discount_reason' => $discount > 0 ? 'Invoice discount' : null,
        ]);

        // Create debit transaction if payment exists
        $debitTransaction = null;
        if ($paidAmount > 0) {
            $debitTransaction = Transaction::create([
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'type' => 'debit',
                'purpose' => 'Payment for Invoice #' . $invoice->invoice_number,
                'amount' => $paidAmount,
                'method' => $request->payment_method,
                'account_id' => $request->account_id, // Accounting integration
                'note' => 'Invoice payment',
                'reference' => $invoice->invoice_number,
                'discount_amount' => 0,
                'discount_reason' => null,
            ]);
        }

        // Allocate payments
        $this->paymentAllocationService->allocatePayments($customer->id);

        // Send SMS for credit transaction
        $creditTransaction->load(['customer', 'invoice']);
        try {
            $this->smsService->sendTransactionSms($creditTransaction);
        } catch (\Exception $e) {
            Log::warning("SMS failed for transaction {$creditTransaction->id}: " . $e->getMessage());
        }
    }

    private function generateUniqueInvoiceNumber()
    {
        do {
            $last_invoice_number = Invoice::max('invoice_number');
            $next_number = $last_invoice_number ? (int) substr($last_invoice_number, 4) + 1 : 1;
            $invoice_number = 'INV-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
        } while (Invoice::where('invoice_number', $invoice_number)->exists());
        return $invoice_number;
    }

    private function resolveCustomerLedgerBalance(Customer $customer): array
    {
        $customerId = $customer->id;
        $openingBalance = (float) ($customer->opening_balance ?? 0);

        $totalCredits = Transaction::where('customer_id', $customerId)
            ->where('type', 'credit')
            ->sum('amount');

        $totalDebits = Transaction::where('customer_id', $customerId)
            ->where('type', 'debit')
            ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
            ->value('total');

        $balance = $openingBalance + $totalCredits - $totalDebits;
        $type = $balance < 0 ? 'credit' : 'debit';
        $absoluteValue = round(abs($balance), 2);
        $signed = $type === 'credit' ? -$absoluteValue : $absoluteValue;
        $formatted = ($type === 'credit' ? '-' : '') . number_format($absoluteValue, 2);

        return [
            'signed' => (float) $signed,
            'absolute' => (float) $absoluteValue,
            'type' => $type,
            'formatted' => $formatted,
        ];
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer.ledgerAccount', 'referrer', 'items.product.category', 'items.product.company', 'items.challanItems']);

        // If AJAX request, return JSON with calculated delivered quantities
        if (request()->ajax() || request()->wantsJson()) {
            // Transform invoice to array and add delivered quantities
            $invoiceData = $invoice->toArray();
            $invoiceData['items'] = $invoice->items->map(function ($item) {
                $itemData = $item->toArray();
                $itemData['delivered_quantity'] = $item->challanItems->sum('quantity');
                $itemData['product'] = $item->product;
                return $itemData;
            })->toArray();

            return response()->json([
                'invoice' => $invoiceData
            ]);
        }

        // Calculate total weight for display
        $totalWeight = 0;
        foreach ($invoice->items as $item) {
            $product = $item->product;
            $category = $product->category ?? null;

            // Prioritize product weight over category weight
            $weightValue = null;
            $weightUnit = null;

            if ($product && !empty($product->weight_value) && !empty($product->weight_unit)) {
                $weightValue = (float) $product->weight_value;
                $weightUnit = $product->weight_unit;
            } elseif ($category && !empty($category->weight_value) && !empty($category->weight_unit)) {
                $weightValue = (float) $category->weight_value;
                $weightUnit = $category->weight_unit;
            }

            if (!$weightValue || !$weightUnit) {
                continue;
            }

            $quantity = (float) $item->quantity;
            $boxes = (float) ($item->boxes ?? 0);
            $pieces = (float) ($item->pieces ?? 0);

            // For per_unit, just multiply quantity by weight
            if ($weightUnit === 'per_unit') {
                $totalWeight += $quantity * $weightValue;
                continue;
            }

            // For per_piece and per_box, we need category info
            $boxPcs = $category ? (float) ($category->box_pcs ?? 0) : 0;
            $piecesFeet = $category ? (float) ($category->pieces_feet ?? 0) : 0;

            $totalPieces = 0;
            if ($boxPcs > 0) {
                $totalPieces = ($boxes * $boxPcs) + $pieces;
            } elseif ($pieces > 0) {
                $totalPieces = $pieces;
            } elseif ($piecesFeet > 0 && $quantity > 0) {
                $totalPieces = $quantity / $piecesFeet;
            }

            if ($weightUnit === 'per_piece') {
                $totalWeight += $totalPieces * $weightValue;
            } elseif ($weightUnit === 'per_box') {
                $boxCount = $boxPcs > 0 ? ($totalPieces / $boxPcs) : $boxes;
                $totalWeight += $boxCount * $weightValue;
            }
        }

        $ledgerData = $this->resolveCustomerLedgerBalance($invoice->customer);
        $ledgerOutstanding = $ledgerData['absolute'];
        $ledgerType = $ledgerData['type'];

        return view('invoices.show', compact('invoice', 'totalWeight', 'ledgerOutstanding', 'ledgerType'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product.category', 'items.product.company']);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'address']);
        $referrers = Referrer::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::with(['company', 'category'])->orderBy('name')->get(['id', 'name', 'sale_price', 'current_stock', 'purchase_price', 'category_id', 'company_id']);

        // Get companies and categories for the new product modal
        $companies = Company::brands()->orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'box_pcs', 'pieces_feet', 'weight_value', 'weight_unit']);

        // Get cash/bank accounts for accounting integration
        $cashBankAccounts = AutoPostingService::getCashBankAccounts();
        $customerLedgerBalance = $this->resolveCustomerLedgerBalance($invoice->customer);

        return view('invoices.edit', compact(
            'invoice',
            'customers',
            'referrers',
            'products',
            'companies',
            'categories',
            'cashBankAccounts',
            'customerLedgerBalance'
        ));
    }

public function update(Request $request, Invoice $invoice)
{
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'referrer_id' => 'nullable|exists:referrers,id',
            'referrer_compensated' => 'nullable|boolean',
            'invoice_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'account_id' => 'nullable|exists:accounts,id',
            'invoice_type' => 'required|in:tiles,other',
            'delivery_status' => 'required|in:pending,partial,delivered',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'description' => 'required|array',
            'code.*' => 'nullable|string|max:255',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'unit_price' => 'required|array',
            'unit_price.*' => 'numeric|min:0',
            'item_total' => 'required|array',
            'item_total.*' => 'numeric|min:0',
            'force_negative_stock' => 'nullable|boolean'
        ]);

    DB::beginTransaction();
    try {
        $oldCustomerId = $invoice->customer_id;
        $wasDelivered = $invoice->delivery_status === 'delivered';
        
        // Store old total and paid amount before deleting transactions
        $oldTotal = $invoice->total;
        $oldPaidAmount = Transaction::where('invoice_id', $invoice->id)
            ->where('type', 'debit')
            ->sum('amount');
        
        // Restore stock if previously delivered
        if ($wasDelivered) {
            foreach ($invoice->items as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock += $item->quantity;
                $product->save();
            }
        }

        // Stock validation for delivered status
        $stockIssues = [];
        $isNowDelivered = $request->delivery_status === 'delivered';
        if ($isNowDelivered && !$request->force_negative_stock) {
            $products = Product::whereIn('id', array_filter($request->product_id))->get()->keyBy('id');
            foreach ($request->product_id as $key => $product_id) {
                if (!$product_id) continue;
                $product = $products[$product_id];
                $quantity = $request->quantity[$key];
                if ($quantity > $product->current_stock) {
                    $stockIssues[] = [
                        'name' => $product->name,
                        'available' => $product->current_stock,
                        'required' => $quantity,
                        'shortage' => $quantity - $product->current_stock
                    ];
                }
            }
            if (!empty($stockIssues)) {
                DB::rollBack();
                return response()->json(['success' => false, 'stock_issues' => $stockIssues], 422);
            }
        }

        // Delete old transactions related to this invoice
        Transaction::where('invoice_id', $invoice->id)->delete();

        $referrerId = $request->referrer_id;
        $referrerCompensated = $referrerId ? (bool) $request->referrer_compensated : false;

        // Update invoice
        $invoice->update([
            'customer_id' => $request->customer_id,
            'referrer_id' => $referrerId,
            'referrer_compensated' => $referrerCompensated,
            'invoice_date' => $request->invoice_date,
            'subtotal' => $request->subtotal,
            'discount' => $request->discount ?? 0,
            'total' => $request->total,
            'paid_amount' => 0, // Will be recalculated by allocation service
            'due_amount' => $request->total, // Will be recalculated by allocation service
            'payment_method' => $request->payment_method,
            'payment_status' => 'due', // Will be recalculated by allocation service
            'notes' => $request->notes,
            'invoice_type' => $request->invoice_type,
            'sales_account_id' => $this->resolveSalesAccountId($request->invoice_type),
            'delivery_status' => $request->delivery_status ?? 'pending'
        ]);

        // Delete old items
        InvoiceItem::withoutGlobalScopes()->where('invoice_id', $invoice->id)->delete();

        // Create new items
        $itemTenantId = $invoice->tenant_id ?? TenantContext::currentId();
        $items = [];
        foreach ($request->product_id as $key => $product_id) {
            if (!$product_id) continue;
            $item = [
                'invoice_id' => $invoice->id,
                'tenant_id' => $itemTenantId,
                'product_id' => $product_id,
                'description' => $request->description[$key],
                'code' => $request->code[$key] ?? null,
                'quantity' => $request->quantity[$key],
                'unit_price' => $request->unit_price[$key],
                'total' => $request->item_total[$key],
                'created_at' => now(),
                'updated_at' => now()
            ];
            if ($request->invoice_type === 'tiles') {
                $item['boxes'] = $request->boxes[$key] ?? 0;
                $item['pieces'] = $request->pieces[$key] ?? 0;
            }
            $items[] = $item;

            // Update stock if delivered
            if ($isNowDelivered) {
                $product = Product::findOrFail($product_id);
                $product->current_stock -= $request->quantity[$key];
                $product->save();
            }
        }
        InvoiceItem::insert($items);

        // Create credit transaction for the new invoice total
        Transaction::create([
            'customer_id' => $request->customer_id,
            'invoice_id' => $invoice->id,
            'type' => 'credit',
            'purpose' => 'Invoice #' . $invoice->invoice_number,
            'amount' => $request->total, // Total after discount
            'method' => $request->payment_method,
            'note' => 'Invoice sale (updated)',
            'reference' => $invoice->invoice_number,
            'discount_amount' => 0,
            'discount_reason' => null,
        ]);

        // If there was a previous payment, recreate it
        if ($oldPaidAmount > 0) {
            Transaction::create([
                'customer_id' => $request->customer_id,
                'invoice_id' => $invoice->id,
                'type' => 'debit',
                'purpose' => 'Payment for Invoice #' . $invoice->invoice_number,
                'amount' => $oldPaidAmount,
                'method' => $request->payment_method,
                'account_id' => $request->account_id, // Accounting integration
                'note' => 'Invoice payment (preserved from original)',
                'reference' => $invoice->invoice_number,
                'discount_amount' => 0,
                'discount_reason' => null,
            ]);
        }

        // Re-allocate payments for affected customers
        $this->paymentAllocationService->allocatePayments($request->customer_id);
        if ($oldCustomerId != $request->customer_id) {
            $this->paymentAllocationService->allocatePayments($oldCustomerId);
        }

        DB::commit();

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'invoice_number' => $invoice->invoice_number,
                'redirect' => route('invoices.show', $invoice)
            ]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice update failed: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Invoice update failed: ' . $e->getMessage())->withInput();
    }
}

public function destroy(Invoice $invoice)
{
    DB::beginTransaction();
    try {
        // Check if invoice can be deleted
        if ($invoice->delivery_status !== 'pending') {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete invoice with non-pending delivery status. Only pending invoices can be deleted.'
            ], 422);
        }

        // Store values before deletion
        $customerId = $invoice->customer_id;
        $invoiceNumber = $invoice->invoice_number;
        
        // Handle balance adjustment before deletion
        $this->paymentAllocationService->handleInvoiceDeletion($invoice);
        
        // Delete related transactions
        Transaction::where('invoice_id', $invoice->id)->delete();
        
        // Delete related items
        InvoiceItem::withoutGlobalScopes()->where('invoice_id', $invoice->id)->delete();

        // Delete the invoice itself
        $invoice->delete();

        // Re-allocate remaining payments to other invoices
        $this->paymentAllocationService->allocatePayments($customerId);

        DB::commit();
        
        Log::info('Invoice #' . $invoiceNumber . ' deleted successfully by user ' . Auth::id());
        
        return response()->json([
            'success' => true, 
            'message' => 'Invoice #' . $invoiceNumber . ' deleted successfully.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to delete invoice #' . ($invoice->invoice_number ?? 'unknown') . ': ' . $e->getMessage());
        
        return response()->json([
            'success' => false, 
            'message' => 'Failed to delete invoice: ' . $e->getMessage()
        ], 500);
    }
}
    public function print(Request $request, Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product.category', 'items.product.company']);

        $allowedTemplates = array_keys($this->invoiceTemplateOptions());
        $settings = app('business.settings');

        $defaultTemplate = (string) ($settings->invoice_template ?? 'standard');
        if (!in_array($defaultTemplate, $allowedTemplates, true)) {
            $defaultTemplate = 'standard';
        }

        $selectedTemplate = (string) $request->query('template', $defaultTemplate);
        if (!in_array($selectedTemplate, $allowedTemplates, true)) {
            $selectedTemplate = $defaultTemplate;
        }

        $printOptions = array_merge(
            $this->defaultInvoicePrintOptions(),
            (array) ($settings->invoice_print_options ?? [])
        );

        if ($request->boolean('preview')) {
            $booleanKeys = [
                'show_company_phone',
                'show_company_email',
                'show_company_address',
                'show_company_bin',
                'show_bank_details',
                'show_terms',
                'show_footer_message',
                'show_customer_qr',
                'show_signatures',
            ];

            foreach ($booleanKeys as $key) {
                if ($request->has($key)) {
                    $printOptions[$key] = $request->boolean($key);
                }
            }

            if ($request->has('invoice_phone_override')) {
                $printOptions['invoice_phone_override'] = trim((string) $request->query('invoice_phone_override', ''));
            }
        }

        return view('invoices.print', compact('invoice', 'selectedTemplate', 'printOptions'));
    }

    private function invoiceTemplateOptions(): array
    {
        return [
            'standard' => 'Standard',
            'modern' => 'Modern',
            'simple' => 'Simple',
            'bold' => 'Bold',
            'elegant' => 'Elegant',
            'imaginative' => 'Imaginative',
        ];
    }

    private function defaultInvoicePrintOptions(): array
    {
        return [
            'show_company_phone' => true,
            'show_company_email' => true,
            'show_company_address' => true,
            'show_company_bin' => true,
            'show_bank_details' => true,
            'show_terms' => true,
            'show_footer_message' => true,
            'show_customer_qr' => true,
            'show_signatures' => true,
            'invoice_phone_override' => '',
        ];
    }

public function getProductDetails($id, Request $request)
{
    $product = Product::with(['company', 'category'])->findOrFail($id);
    $customerId = $request->input('customer_id');
    
    // Default to product's sale price
    $defaultPrice = $product->sale_price;
    
    // If customer is selected, check for last price paid by this customer
    if ($customerId) {
        $lastPrice = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.customer_id', $customerId)
            ->where('invoice_items.product_id', $id)
            ->orderBy('invoices.invoice_date', 'desc')
            ->orderBy('invoices.id', 'desc')
            ->value('invoice_items.unit_price');
        
        // Use last price if found, otherwise use default
        if ($lastPrice !== null) {
            $defaultPrice = $lastPrice;
        }
    }
    
    // Add the calculated price to the response
    $product->customer_price = $defaultPrice;
    
    return response()->json($product);
}

public function getCustomerDetails($id)
{
        try {
            $this->paymentAllocationService->allocatePayments($id);
        } catch (\Exception $e) {
            Log::warning("Customer details refresh failed for customer {$id}: " . $e->getMessage());
        }

    $customer = Customer::with('ledgerAccount')->findOrFail($id, ['id', 'name', 'phone', 'address', 'outstanding_balance', 'opening_balance']);
    
    $ledgerBalance = $this->resolveCustomerLedgerBalance($customer);

    return response()->json([
        'id' => $customer->id,
        'name' => $customer->name,
        'phone' => $customer->phone,
        'address' => $customer->address,
        'outstanding_balance' => $customer->outstanding_balance,
        'opening_balance' => $customer->opening_balance,
        'ledger_balance' => $ledgerBalance['signed'],
        'ledger_balance_type' => $ledgerBalance['type'],
        'ledger_balance_formatted' => $ledgerBalance['formatted'],
    ]);
}

    public function updateDeliveryStatus(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'delivery_status' => 'required|in:pending,partial,delivered'
        ]);

        $invoice = Invoice::with('items')->findOrFail($request->invoice_id);
        $oldStatus = $invoice->delivery_status;
        $newStatus = $request->delivery_status;

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true, 'message' => 'No change in status']);
        }

        DB::beginTransaction();
        try {
            $itemsWithQuantities = $invoice->items->map(function ($item) {
                $deliveredQuantity = $item->getDeliveredQuantityAttribute();
                return [
                    'item' => $item,
                    'delivered' => $deliveredQuantity,
                    'remaining' => $item->quantity - $deliveredQuantity
                ];
            });

            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                foreach ($itemsWithQuantities as $itemData) {
                    if ($itemData['remaining'] > 0) {
                        $product = Product::findOrFail($itemData['item']->product_id);
                        $product->current_stock -= $itemData['remaining'];
                        $product->save();
                    }
                }
            } elseif ($oldStatus === 'delivered' && $newStatus !== 'delivered') {
                foreach ($itemsWithQuantities as $itemData) {
                    if ($itemData['remaining'] > 0) {
                        $product = Product::findOrFail($itemData['item']->product_id);
                        $product->current_stock += $itemData['remaining'];
                        $product->save();
                    }
                }
            }

            $invoice->delivery_status = $newStatus;
            $invoice->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Delivery status updated successfully',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateReferrerCompensation(Request $request, Invoice $invoice)
    {
        if (!$invoice->referrer_id) {
            return response()->json(['message' => 'No referrer assigned to this invoice.'], 422);
        }

        $invoice->referrer_compensated = $request->boolean('referrer_compensated');
        $invoice->save();

        return response()->json([
            'success' => true,
            'referrer_compensated' => $invoice->referrer_compensated,
        ]);
    }

     public function markAsPaid(Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            // Get the actual remaining due amount after payment allocation
            $remainingDue = $this->paymentAllocationService->getInvoiceRemainingDue($invoice);
            
            if ($remainingDue <= 0) {
                return response()->json(['success' => false, 'message' => 'Invoice already paid or overpaid']);
            }

            // Create payment transaction for the remaining due amount
            $transaction = Transaction::create([
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id,
                'type' => 'debit',
                'purpose' => 'Payment for Invoice #' . $invoice->invoice_number,
                'amount' => $remainingDue,
                'method' => 'cash',
                'date' => now(),
                'note' => 'Invoice marked as paid',
                'reference' => $invoice->invoice_number
            ]);

            // Re-allocate payments to update all invoice statuses
            $this->paymentAllocationService->allocatePayments($invoice->customer_id);
// Send SMS for payment - ADD THIS BLOCK
            try {
                $this->smsService->sendTransactionSms($transaction);
            } catch (\Exception $e) {
                Log::warning("SMS failed for mark as paid transaction {$transaction->id}: " . $e->getMessage());
            }
            DB::commit();
            Log::info('Invoice #' . $invoice->invoice_number . ' marked as paid with amount: ' . $remainingDue);
            
            return response()->json([
                'success' => true, 
                'message' => 'Invoice paid successfully with amount: ৳' . number_format($remainingDue, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function getSummary(Request $request)
{
    try {
        $query = Invoice::query();

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Fix: Remove any ordering before aggregate query and use first() properly
        $totals = $query->selectRaw('COUNT(*) as total_invoices, SUM(total) as total_amount, SUM(paid_amount) as total_paid, SUM(due_amount) as total_due')
            ->first();

        return response()->json([
            'totalInvoices' => $totals->total_invoices ?? 0,
            'totalAmount' => number_format($totals->total_amount ?? 0, 2, '.', ''),
            'totalPaid' => number_format($totals->total_paid ?? 0, 2, '.', ''),
            'totalDue' => number_format($totals->total_due ?? 0, 2, '.', '')
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to fetch invoice summary: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch summary'], 500);
    }
}


    public function getCustomerPurchaseHistory($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId, ['id', 'name', 'phone', 'address']);
            $purchasedProducts = InvoiceItem::query()
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('products', 'invoice_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
                ->where('invoices.customer_id', $customerId)
                ->groupBy('invoice_items.product_id', 'products.name', 'categories.name', 'companies.name')
                ->selectRaw('
                    invoice_items.product_id,
                    products.name as product_name,
                    categories.name as category_name,
                    companies.name as company_name,
                    SUM(invoice_items.quantity) as total_purchased,
                    MAX(invoices.invoice_date) as last_purchase_date,
                    COUNT(DISTINCT invoices.id) as invoice_count
                ')
                ->get();

            $returnedProducts = ProductReturnItem::query()
                ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
                ->where('product_returns.customer_id', $customerId)
                ->groupBy('product_return_items.product_id')
                ->selectRaw('product_return_items.product_id, SUM(product_return_items.quantity) as total_returned')
                ->get()
                ->keyBy('product_id');

            $products = $purchasedProducts->map(function ($purchased) use ($returnedProducts) {
                $returned = $returnedProducts->get($purchased->product_id);
                $returnedQty = $returned ? $returned->total_returned : 0;
                return [
                    'name' => $purchased->product_name,
                    'category' => $purchased->category_name ?? 'N/A',
                    'company' => $purchased->company_name ?? 'N/A',
                    'purchased' => (float) $purchased->total_purchased,
                    'returned' => (float) $returnedQty,
                    'available' => (float) $purchased->total_purchased - (float) $returnedQty,
                    'last_purchase' => Carbon::parse($purchased->last_purchase_date)->format('d M, Y'),
                    'invoice_count' => $purchased->invoice_count
                ];
            })->keyBy('product_id');

            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address
                ],
                'products' => $products,
                'summary' => [
                    'total_products' => count($products),
                    'total_purchased' => $products->sum('purchased'),
                    'total_returned' => $products->sum('returned'),
                    'total_available' => $products->sum('available')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading purchase history: ' . $e->getMessage()], 500);
        }
    }
    public function getModalDetails(Invoice $invoice)
{
    try {
        // Load relationships
        $invoice->load([
            'customer:id,name,phone,address',
            'items.product.category:id,name',
            'items.product.company:id,name'
        ]);

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'customer' => [
                    'name' => $invoice->customer->name,
                    'phone' => $invoice->customer->phone,
                    'address' => $invoice->customer->address
                ],
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'total' => $invoice->total,
                'paid_amount' => $invoice->paid_amount,
                'due_amount' => $invoice->due_amount,
                'payment_status' => $invoice->payment_status,
                'delivery_status' => $invoice->delivery_status,
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                        'boxes' => $item->boxes,
                        'pieces' => $item->pieces,
                        'description' => $item->description,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'category' => $item->product->category ? [
                                'id' => $item->product->category->id,
                                'name' => $item->product->category->name
                            ] : null,
                            'company' => $item->product->company ? [
                                'id' => $item->product->company->id,
                                'name' => $item->product->company->name
                            ] : null
                        ]
                    ];
                })
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Error loading invoice modal details: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load invoice details: ' . $e->getMessage()
        ], 500);
    }
}
}
