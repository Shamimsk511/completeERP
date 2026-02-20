<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentAllocationService;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TransactionSmsService;
use App\Services\Accounting\AutoPostingService;
use App\Models\Accounting\Account;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    protected $paymentAllocationService;
    protected $smsService;
    protected $autoPostingService;

    function __construct(
        PaymentAllocationService $paymentAllocationService,
        TransactionSmsService $smsService,
        AutoPostingService $autoPostingService
    ) {
        $this->paymentAllocationService = $paymentAllocationService;
        $this->smsService = $smsService;
        $this->autoPostingService = $autoPostingService;
        $this->middleware('permission:transaction-list|transaction-create|transaction-edit|transaction-delete', ['only' => ['index', 'show', 'customerLedger']]);
        $this->middleware('permission:transaction-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:transaction-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:transaction-delete', ['only' => ['destroy']]);
    }
    
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Transaction::query()
                ->select([
                    'transactions.id',
                    'transactions.customer_id',
                    'transactions.type',
                    'transactions.amount',
                    'transactions.method',
                    'transactions.purpose',
                    'transactions.created_at',
                ])
                ->with('customer:id,name,phone');
            
            // Apply filters
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereDate('created_at', '>=', $request->date_from)
                      ->whereDate('created_at', '<=', $request->date_to);
            }
            
            if ($request->filled('customer_id') && $request->customer_id != '') {
                $query->where('customer_id', $request->customer_id);
            }
            
            if ($request->filled('type') && $request->type != '') {
                $query->where('type', $request->type);
            }
            
            if ($request->filled('method') && $request->method != '') {
                $query->where('method', $request->method);
            }
            
            if ($request->filled('purpose') && $request->purpose != '') {
                $query->where('purpose', 'like', '%' . $request->purpose . '%');
            }
            
            // Calculate statistics for filtered data
            $stats = $this->calculateStats($query);
            
            return DataTables::of($query)
                ->addColumn('customer', function ($transaction) {
                    $customerName = $transaction->customer ? $transaction->customer->name : 'N/A';
                    $customerPhone = $transaction->customer ? $transaction->customer->phone : '';

                    if (!$transaction->customer) {
                        return 'N/A';
                    }

                    $customerLink = route('customers.show', $transaction->customer_id);
                    $nameHtml = '<a href="' . $customerLink . '">' . e($customerName) . '</a>';

                    if ($customerPhone) {
                        return $nameHtml . '<br><small class="text-muted">' . e($customerPhone) . '</small>';
                    }
                    return $nameHtml;
                })
                ->editColumn('created_at', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d H:i:s');
                })
                ->editColumn('date', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d');
                })
                ->editColumn('amount', function ($transaction) {
                    return number_format($transaction->amount, 2);
                })
                ->editColumn('method', function ($transaction) {
                    $methods = [
                        'cash' => 'Cash',
                        'bank' => 'Bank',
                        'mobile_bank' => 'Mobile Bank',
                        'cheque' => 'Cheque'
                    ];
                    return $methods[$transaction->method] ?? ucfirst($transaction->method);
                })
                ->addColumn('actions', function ($transaction) {
                    return view('transactions.partials.action-buttons', compact('transaction'))->render();
                })
                ->rawColumns(['customer', 'actions'])
                ->with([
                    'stats' => $stats
                ])
                ->make(true);
        }
        
        // For regular page loads
        $customers = Customer::orderBy('name')->get();
        
        return view('transactions.index', compact('customers'));
    }
    
    private function calculateStats($query)
    {
        $statsQuery = (clone $query)->setEagerLoads([])->reorder();
        $statsQuery->getQuery()->columns = null;

        $stats = $statsQuery->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'debit' THEN amount + COALESCE(discount_amount, 0) ELSE 0 END), 0) as total_debit")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as total_credit")
            ->first();

        $totalDebit = (float) ($stats->total_debit ?? 0);
        $totalCredit = (float) ($stats->total_credit ?? 0);

        return [
            'totalTransactions' => (int) ($stats->total_transactions ?? 0),
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'netBalance' => $totalDebit - $totalCredit
        ];
    }
    
public function create(Request $request)
{
    $customers = Customer::all();
    $paymentMethods = ['cash', 'bank', 'mobile_bank', 'cheque'];

    $selectedCustomer = null;
    $selectedInvoice = null;

    // Handle pre-selected customer
    if ($request->has('customer_id')) {
        $selectedCustomer = Customer::find($request->customer_id);
    }

    // Handle pre-selected invoice
    if ($request->has('invoice_id')) {
        $selectedInvoice = Invoice::with('customer')->find($request->invoice_id);
        if ($selectedInvoice && !$selectedCustomer) {
            $selectedCustomer = $selectedInvoice->customer;
        }
    }

    // Get cash/bank accounts for accounting integration
    $cashBankAccounts = AutoPostingService::getCashBankAccounts();

    return view('transactions.create', compact('customers', 'paymentMethods', 'selectedCustomer', 'selectedInvoice', 'cashBankAccounts'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:debit,credit',
            'purpose' => 'required|string|max:255',
            'method' => 'required|in:cash,bank,mobile_bank,cheque',
            'account_id' => 'nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'notes' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($request->customer_id);
            
            // Normalize optional fields
            $validated['discount_amount'] = $request->discount_amount ?? 0;
            $validated['note'] = $request->input('note', $request->input('notes'));
            unset($validated['notes']);
            
            // Create transaction
            $transaction = Transaction::create($validated);
            
            // Re-allocate payments to ensure proper invoice payment status
            $this->paymentAllocationService->allocatePayments($request->customer_id);

            // Auto-post to accounting
            try {
                $this->autoPostingService->postTransaction($transaction);
            } catch (\Exception $e) {
                // Log accounting error but don't fail the transaction
                Log::warning("Auto-posting failed for transaction {$transaction->id}: " . $e->getMessage());
            }

            // Send SMS notification
            $transaction->load(['customer', 'invoice']); // Load customer for SMS
            try {
                $this->smsService->sendTransactionSms($transaction);
            } catch (\Exception $e) {
                // Log SMS error but don't fail the transaction
                Log::warning("SMS failed for transaction {$transaction->id}: " . $e->getMessage());
            }
            DB::commit();
            
            $message = 'Transaction created successfully.';
            if ($request->discount_amount > 0) {
                $message .= ' Discount of ৳' . number_format($request->discount_amount, 2) . ' applied.';
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'transaction' => $transaction
                ]);
            }
            
            return redirect()->route('transactions.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction failed: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Transaction failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'invoice', 'account']);
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $customers = Customer::orderBy('name')->get();
        $transaction->load('customer');
        $cashBankAccounts = AutoPostingService::getCashBankAccounts();
        return view('transactions.edit', compact('transaction', 'customers', 'cashBankAccounts'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:debit,credit',
            'purpose' => 'required|string|max:255',
            'method' => 'required|in:cash,bank,mobile_bank,cheque',
            'account_id' => 'nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'notes' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $oldCustomerId = $transaction->customer_id;
            
            // Update transaction
            $validated['discount_amount'] = $request->discount_amount ?? 0;
            $validated['note'] = $request->input('note', $request->input('notes'));
            unset($validated['notes']);
            $transaction->update($validated);
            
            // Re-allocate payments for affected customers
            $this->paymentAllocationService->allocatePayments($request->customer_id);
            if ($oldCustomerId != $request->customer_id) {
                $this->paymentAllocationService->allocatePayments($oldCustomerId);
            }
            // Send SMS notification for updated transaction - ADD THIS BLOCK
            try {
                $this->smsService->sendTransactionSms($transaction);
            } catch (\Exception $e) {
                Log::warning("SMS failed for updated transaction {$transaction->id}: " . $e->getMessage());
            }
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction updated successfully.'
                ]);
            }
            
            return redirect()->route('transactions.index')
                ->with('success', 'Transaction updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction update failed: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Transaction update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Transaction $transaction)
    {
        DB::beginTransaction();
        try {
            $customerId = $transaction->customer_id;
            
            // Delete the transaction
            $transaction->deleted_by = auth()->id();
            $transaction->save();
            $transaction->delete();
            
            // Re-allocate payments to recalculate customer balance
            $this->paymentAllocationService->allocatePayments($customerId);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction moved to trash successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction deletion failed: ' . $e->getMessage()
            ], 422);
        }
    }

// Customer Ledger Functionality

public function customerLedger($customerId)
{
        try {
            $this->paymentAllocationService->allocatePayments($customerId);
        } catch (\Exception $e) {
            Log::warning("Customer ledger refresh failed for customer {$customerId}: " . $e->getMessage());
        }

        $customers = Customer::findOrFail($customerId);
    
    // Get all transactions with relationships (sorting will be handled in the view)
    $transactions = Transaction::where('customer_id', $customerId)
        ->with(['invoice:id,invoice_number']) // Load invoice relationship if exists
        ->get(); // Get all transactions, sorting handled in view for proper balance calculation

    $chronologicalTransactions = $transactions->sortBy(function ($transaction) {
        return $transaction->created_at->timestamp . '.' . str_pad($transaction->id, 10, '0', STR_PAD_LEFT);
    });

    $closingBalance = $customers->opening_balance;
    foreach ($chronologicalTransactions as $transaction) {
        if ($transaction->type == 'credit') {
            $closingBalance += $transaction->amount;
        } else {
            $closingBalance -= ($transaction->amount + ($transaction->discount_amount ?? 0));
        }
    }

    return view('transactions.ledger', compact('customers', 'transactions', 'closingBalance'));
}

    public function printCustomerLedger($customerId)
    {
        try {
            $this->paymentAllocationService->allocatePayments($customerId);
        } catch (\Exception $e) {
            Log::warning("Customer ledger print refresh failed for customer {$customerId}: " . $e->getMessage());
        }

        $customer = Customer::findOrFail($customerId);
        $transactions = Transaction::where('customer_id', $customerId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('transactions.ledger-print', compact('customer', 'transactions'));
    }

    public function getCustomerInvoices($customerId)
    {
        try {
            // Ensure payments are properly allocated first
            $this->paymentAllocationService->allocatePayments($customerId);
            
            $invoices = Invoice::where('customer_id', $customerId)
                ->where('payment_status', '!=', 'paid')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'invoice_number', 'due_amount', 'total']);
            
            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load invoices'
            ], 500);
        }
    }
}
