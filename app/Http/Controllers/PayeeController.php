<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Payee;
use App\Models\PayableTransaction;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\AccountEntry;
use App\Models\PayeeInstallment;
use App\Models\PayeeKistiSkip;
use App\Services\PayeeAccountService;
use App\Services\PayeeLoanService;
use App\Services\Accounting\AutoPostingService;
use App\Services\Accounting\OpeningBalanceService;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PayeeController extends Controller
{
    protected PayeeAccountService $payeeAccountService;
    protected PayeeLoanService $payeeLoanService;
    protected AutoPostingService $autoPostingService;
    protected OpeningBalanceService $openingBalanceService;

public function __construct()
{
    $this->payeeAccountService = app(PayeeAccountService::class);
    $this->payeeLoanService = app(PayeeLoanService::class);
    $this->autoPostingService = app(AutoPostingService::class);
    $this->openingBalanceService = app(OpeningBalanceService::class);
    $this->middleware('permission:payee-list|payee-create|payee-edit|payee-delete', ['only' => ['index', 'show']]);
    $this->middleware('permission:payee-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:payee-edit', ['only' => ['edit', 'update', 'accrueInterest', 'addKistiSkip', 'payInterest']]);
    $this->middleware('permission:payee-delete', ['only' => ['destroy']]);
    $this->middleware('permission:payee-ledger', ['only' => ['ledger', 'printLedger']]);
    $this->middleware('permission:payee-reports', ['only' => ['agingReport', 'detailedAgingReport']]);
}

    public function index(Request $request)
    {
        $cashInSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_in_total'))
            ->where('transaction_type', 'cash_in')
            ->groupBy('payee_id');
        $cashOutSub = DB::table('payable_transactions')
            ->select('payee_id', DB::raw('SUM(amount) as cash_out_total'))
            ->where('transaction_type', 'cash_out')
            ->groupBy('payee_id');

        $ledgerBalanceExpression = "
            CASE
                WHEN COALESCE(payees.opening_balance, 0) = 0
                    AND COALESCE(payee_cash_in.cash_in_total, 0) = 0
                    AND COALESCE(payee_cash_out.cash_out_total, 0) = 0
                    AND COALESCE(payees.current_balance, 0) <> 0
                THEN COALESCE(payees.current_balance, 0)
                ELSE
                    COALESCE(payees.opening_balance, 0)
                    + COALESCE(payee_cash_out.cash_out_total, 0)
                    - COALESCE(payee_cash_in.cash_in_total, 0)
            END
        ";

        if ($request->ajax()) {
            $query = Payee::query()
                ->leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
                    $join->on('payees.id', '=', 'payee_cash_in.payee_id');
                })
                ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
                    $join->on('payees.id', '=', 'payee_cash_out.payee_id');
                })
                ->select('payees.*')
                ->addSelect(DB::raw('COALESCE(payee_cash_in.cash_in_total, 0) as cash_in_total'))
                ->addSelect(DB::raw('COALESCE(payee_cash_out.cash_out_total, 0) as cash_out_total'))
                ->addSelect(DB::raw($ledgerBalanceExpression . ' as ledger_balance'));

            if ($request->filled('category')) {
                $category = $request->input('category');
                $query->where(function ($q) use ($category) {
                    $q->where('category', $category)
                        ->orWhere('type', $category);
                });
            }
            
            return DataTables::of($query)
                ->addColumn('action', function ($payee) {
                    return '
                        <div class="btn-group">
                            <a href="' . route('payees.show', $payee->id) . '" class="btn modern-btn modern-btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="' . route('payees.ledger', $payee->id) . '" class="btn modern-btn modern-btn-primary btn-sm">
                <i class="fas fa-book"></i>
            </a>
                            <a href="' . route('payable-transactions.create', ['payee_id' => $payee->id]) . '" class="btn modern-btn modern-btn-success btn-sm">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a href="' . route('payees.edit', $payee->id) . '" class="btn modern-btn modern-btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn modern-btn modern-btn-danger btn-sm delete-payee" data-id="' . $payee->id . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->editColumn('ledger_balance', function ($payee) {
                    return number_format($payee->ledger_balance ?? 0, 2);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Get summary data for the dashboard
        $totalPayable = Payee::leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_in.payee_id');
            })
            ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_out.payee_id');
            })
            ->sum(DB::raw($ledgerBalanceExpression));
        $totalSuppliers = Payee::where('category', 'supplier')->count();
        $totalIndividuals = Payee::where('category', 'personal')->count();
        $totalPayees = Payee::count();
        $topPayees = Payee::leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_in.payee_id');
            })
            ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
                $join->on('payees.id', '=', 'payee_cash_out.payee_id');
            })
            ->select('payees.*', DB::raw($ledgerBalanceExpression . ' as ledger_balance'))
            ->orderByDesc('ledger_balance')
            ->take(5)
            ->get();

        $categories = $this->getPayeeCategories();

        return view('payables.payees.index', compact('totalPayable', 'totalSuppliers', 'totalIndividuals', 'totalPayees', 'topPayees', 'categories'));
    }

    public function create()
    {
        $categories = $this->getPayeeCategories();
        return view('payables.payees.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'category' => 'required|in:supplier,bank,personal,cc,sme,term_loan,daily_kisti',
            'opening_balance' => 'nullable|numeric',
            'principal_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0',
            'loan_start_date' => 'nullable|date',
            'loan_term_months' => 'nullable|integer|min:1',
            'daily_kisti_amount' => 'nullable|numeric|min:0',
            'daily_kisti_start_date' => 'nullable|date',
        ]);

        $category = $validated['category'];
        $validated['type'] = $category;
        $validated['daily_kisti_amount'] = $validated['daily_kisti_amount'] ?? 0;
        $validated['principal_amount'] = $validated['principal_amount'] ?? 0;
        $validated['interest_rate'] = $validated['interest_rate'] ?? 0;

        if (in_array($category, ['cc', 'sme', 'term_loan', 'daily_kisti'], true)) {
            $principal = (float) ($validated['principal_amount'] ?? 0);
            $validated['principal_balance'] = $principal;
            $validated['current_balance'] = $principal;
            $validated['opening_balance'] = $validated['opening_balance'] ?? $principal;
            if ($category === 'cc') {
                $validated['interest_accrued'] = 0;
                $validated['interest_last_accrual_date'] = $validated['loan_start_date'] ?? now()->toDateString();
            }
            if ($category === 'daily_kisti' && empty($validated['daily_kisti_start_date'])) {
                $validated['daily_kisti_start_date'] = $validated['loan_start_date'] ?? now()->toDateString();
            }
        } else {
            $validated['current_balance'] = $validated['opening_balance'] ?? 0;
        }

        // Set current balance equal to opening balance initially
        $payee = Payee::create($validated);

        // Create or sync ledger account for payee
        $this->payeeAccountService->ensureAccountForPayee($payee);
        $this->openingBalanceService->postPayeeOpeningBalance(
            $payee,
            (float) ($payee->opening_balance ?? 0),
            'credit',
            now()->toDateString(),
            auth()->id()
        );

        if ($payee->isSmeLoan()) {
            $this->payeeLoanService->buildSmeSchedule($payee);
        }

        return redirect()->route('payees.index')
            ->with('success', 'Payee created successfully.');
    }

    public function show(Payee $payee)
    {
        $interestPreview = null;
        $installments = null;
        $kistiSummary = null;
        $cashBankAccounts = AutoPostingService::getCashBankAccounts();
        $account = $this->getPayeeLedgerAccount($payee);
        $ledgerStats = $this->getLedgerStats($payee, $account);

        if ($payee->isCcLoan()) {
            $interestPreview = $this->payeeLoanService->getCcInterestPreview($payee, Carbon::today());
        }

        if ($payee->isSmeLoan()) {
            $installments = $payee->installments()->orderBy('installment_number')->get();
        }

        if ($payee->isDailyKisti()) {
            $kistiSummary = $this->payeeLoanService->getDailyKistiSummary($payee, Carbon::today());
        }

        $cashIn = (float) $payee->transactions()->where('transaction_type', 'cash_in')->sum('amount');
        $cashOut = (float) $payee->transactions()->where('transaction_type', 'cash_out')->sum('amount');
        $ledgerOpeningBalance = (float) ($payee->opening_balance ?? 0);
        $ledgerCurrentBalance = $ledgerOpeningBalance + $cashOut - $cashIn;
        if (abs($ledgerCurrentBalance) < 0.0001 && abs($ledgerOpeningBalance) < 0.0001 && abs($cashIn) < 0.0001 && abs($cashOut) < 0.0001) {
            $legacyBalance = (float) ($payee->current_balance ?? 0);
            if (abs($legacyBalance) > 0.0001) {
                $ledgerOpeningBalance = (float) ($payee->opening_balance ?? 0);
                $ledgerCurrentBalance = $legacyBalance;
            }
        }

        return view('payables.payees.show', compact(
            'payee',
            'interestPreview',
            'installments',
            'kistiSummary',
            'cashBankAccounts',
            'ledgerOpeningBalance',
            'ledgerCurrentBalance',
            'ledgerStats'
        ));
    }

    public function edit(Payee $payee)
    {
        $categories = $this->getPayeeCategories();
        return view('payables.payees.edit', compact('payee', 'categories'));
    }

    public function update(Request $request, Payee $payee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'category' => 'required|in:supplier,bank,personal,cc,sme,term_loan,daily_kisti',
            'opening_balance' => 'nullable|numeric',
            'principal_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0',
            'loan_start_date' => 'nullable|date',
            'loan_term_months' => 'nullable|integer|min:1',
            'daily_kisti_amount' => 'nullable|numeric|min:0',
            'daily_kisti_start_date' => 'nullable|date',
        ]);

        $category = $validated['category'];
        $validated['type'] = $category;
        $validated['daily_kisti_amount'] = $validated['daily_kisti_amount'] ?? 0;
        $validated['principal_amount'] = $validated['principal_amount'] ?? ($payee->principal_amount ?? 0);
        $validated['interest_rate'] = $validated['interest_rate'] ?? ($payee->interest_rate ?? 0);

        if (in_array($category, ['cc', 'sme', 'term_loan', 'daily_kisti'], true)) {
            $principal = (float) ($validated['principal_amount'] ?? $payee->principal_amount ?? 0);
            $validated['principal_balance'] = $payee->principal_balance > 0 ? $payee->principal_balance : $principal;
            $validated['current_balance'] = $validated['principal_balance'] + ($category === 'cc' ? ($payee->interest_accrued ?? 0) : 0);
            if ($category === 'daily_kisti' && empty($validated['daily_kisti_start_date'])) {
                $validated['daily_kisti_start_date'] = $payee->daily_kisti_start_date ?? $validated['loan_start_date'] ?? now()->toDateString();
            }
        }

        $payee->update($validated);

        // Sync ledger account name
        $this->syncPayeeLedgerAccount($payee);
        $this->openingBalanceService->postPayeeOpeningBalance(
            $payee,
            (float) ($payee->opening_balance ?? 0),
            'credit',
            now()->toDateString(),
            auth()->id()
        );

        if ($payee->isSmeLoan()) {
            $this->payeeLoanService->buildSmeSchedule($payee);
        }

        return redirect()->route('payees.index')
            ->with('success', 'Payee updated successfully.');
    }

    public function destroy(Payee $payee)
    {
        // Check if there are transactions
        if ($payee->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete payee with existing transactions.'
            ]);
        }

        $companyId = $payee->company_id;
        $payee->delete();

        if ($companyId) {
            $remaining = Payee::where('company_id', $companyId)->count();
            if ($remaining === 0) {
                $company = Company::find($companyId);
                if ($company && $company->isSupplierType()) {
                    $company->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payee deleted successfully.'
        ]);
    }

    public function ledger(Request $request, Payee $payee)
    {
        $account = $this->getPayeeLedgerAccount($payee);
        if ($account) {
            return $this->ledgerFromEntries($request, $payee, $account);
        }

        if ($request->ajax()) {
            $query = $payee->transactions()
                ->select('*')
                ->orderBy('transaction_date', 'desc');
            
            return DataTables::of($query)
                ->addColumn('balance', function ($row) use ($payee) {
                    // This is a placeholder. In a real implementation, you'd calculate the running balance
                    // based on transaction history up to this point
                    return number_format(0, 2);
                })
                ->editColumn('transaction_date', function ($row) {
                    return $row->transaction_date->format('Y-m-d');
                })
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->editColumn('transaction_type', function ($row) {
                    return $row->transaction_type == 'cash_in' ? 
                        '<span class="badge badge-success">Cash In</span>' : 
                        '<span class="badge badge-danger">Cash Out</span>';
                })
                ->rawColumns(['transaction_type'])
                ->make(true);
        }

        return view('payables.payees.ledger', compact('payee'));
    }

    public function printLedger(Payee $payee, Request $request)
    {
        $account = $this->getPayeeLedgerAccount($payee);
        if ($account) {
            return $this->printLedgerFromEntries($payee, $request, $account);
        }

        $startDate = $request->input('start_date') ? date('Y-m-d', strtotime($request->input('start_date'))) : null;
        $endDate = $request->input('end_date') ? date('Y-m-d', strtotime($request->input('end_date'))) : null;
        
        $query = $payee->transactions()->orderBy('transaction_date', 'asc');
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        
        $transactions = $query->get();
        
        // Calculate running balance
        $runningBalance = $payee->opening_balance;
        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type == 'cash_in') {
                $runningBalance -= $transaction->amount;
            } else {
                $runningBalance += $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;
        }
        
        return view('payables.payees.print-ledger', compact('payee', 'transactions', 'startDate', 'endDate'));
    }

public function agingReport(Request $request)
{
    $referenceDate = $request->input('reference_date')
        ? Carbon::parse($request->input('reference_date'))
        : Carbon::now();

    $cashInSub = DB::table('payable_transactions')
        ->select('payee_id', DB::raw('SUM(amount) as cash_in_total'))
        ->where('transaction_type', 'cash_in')
        ->groupBy('payee_id');
    $cashOutSub = DB::table('payable_transactions')
        ->select('payee_id', DB::raw('SUM(amount) as cash_out_total'))
        ->where('transaction_type', 'cash_out')
        ->groupBy('payee_id');
    $lastCashOutSub = DB::table('payable_transactions')
        ->select('payee_id', DB::raw('MAX(transaction_date) as last_cash_out_date'))
        ->where('transaction_type', 'cash_out')
        ->groupBy('payee_id');

    $ledgerBalanceExpression = "
        CASE
            WHEN COALESCE(payees.opening_balance, 0) = 0
                AND COALESCE(payee_cash_in.cash_in_total, 0) = 0
                AND COALESCE(payee_cash_out.cash_out_total, 0) = 0
                AND COALESCE(payees.current_balance, 0) <> 0
            THEN COALESCE(payees.current_balance, 0)
            ELSE
                COALESCE(payees.opening_balance, 0)
                + COALESCE(payee_cash_out.cash_out_total, 0)
                - COALESCE(payee_cash_in.cash_in_total, 0)
        END
    ";

    $payees = Payee::query()
        ->leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
            $join->on('payees.id', '=', 'payee_cash_in.payee_id');
        })
        ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
            $join->on('payees.id', '=', 'payee_cash_out.payee_id');
        })
        ->leftJoinSub($lastCashOutSub, 'payee_last_cash_out', function ($join) {
            $join->on('payees.id', '=', 'payee_last_cash_out.payee_id');
        })
        ->select('payees.*')
        ->addSelect(DB::raw('COALESCE(payee_cash_in.cash_in_total, 0) as cash_in_total'))
        ->addSelect(DB::raw('COALESCE(payee_cash_out.cash_out_total, 0) as cash_out_total'))
        ->addSelect(DB::raw('payee_last_cash_out.last_cash_out_date as last_cash_out_date'))
        ->addSelect(DB::raw($ledgerBalanceExpression . ' as ledger_balance'))
        ->get()
        ->filter(fn($payee) => (float) $payee->ledger_balance > 0);

    $agingData = [];
    $totals = [
        'current' => 0,
        '1-30' => 0,
        '31-60' => 0,
        '61-90' => 0,
        'over_90' => 0,
        'total' => 0,
    ];

    foreach ($payees as $payee) {
        $lastCashOutDate = $payee->last_cash_out_date
            ? Carbon::parse($payee->last_cash_out_date)
            : $payee->created_at;
        $daysPastDue = $lastCashOutDate->diffInDays($referenceDate);
        $balance = (float) $payee->ledger_balance;

        $payeeData = [
            'id' => $payee->id,
            'name' => $payee->name,
            'type' => $payee->type,
            'current' => 0,
            '1-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            'over_90' => 0,
            'total' => $balance,
        ];

        if ($daysPastDue <= 0) {
            $payeeData['current'] = $balance;
            $totals['current'] += $balance;
        } elseif ($daysPastDue <= 30) {
            $payeeData['1-30'] = $balance;
            $totals['1-30'] += $balance;
        } elseif ($daysPastDue <= 60) {
            $payeeData['31-60'] = $balance;
            $totals['31-60'] += $balance;
        } elseif ($daysPastDue <= 90) {
            $payeeData['61-90'] = $balance;
            $totals['61-90'] += $balance;
        } else {
            $payeeData['over_90'] = $balance;
            $totals['over_90'] += $balance;
        }

        $agingData[] = $payeeData;
        $totals['total'] += $balance;
    }

    return view('payables.reports.aging', compact('agingData', 'totals', 'referenceDate'));
}

public function detailedAgingReport(Request $request)
{
    $referenceDate = $request->input('reference_date')
        ? Carbon::parse($request->input('reference_date'))
        : Carbon::now();

    $cashInSub = DB::table('payable_transactions')
        ->select('payee_id', DB::raw('SUM(amount) as cash_in_total'))
        ->where('transaction_type', 'cash_in')
        ->groupBy('payee_id');
    $cashOutSub = DB::table('payable_transactions')
        ->select('payee_id', DB::raw('SUM(amount) as cash_out_total'))
        ->where('transaction_type', 'cash_out')
        ->groupBy('payee_id');

    $payees = Payee::query()
        ->leftJoinSub($cashInSub, 'payee_cash_in', function ($join) {
            $join->on('payees.id', '=', 'payee_cash_in.payee_id');
        })
        ->leftJoinSub($cashOutSub, 'payee_cash_out', function ($join) {
            $join->on('payees.id', '=', 'payee_cash_out.payee_id');
        })
        ->select('payees.*')
        ->addSelect(DB::raw('COALESCE(payee_cash_in.cash_in_total, 0) as cash_in_total'))
        ->addSelect(DB::raw('COALESCE(payee_cash_out.cash_out_total, 0) as cash_out_total'))
        ->with(['transactions' => function ($query) {
            $query->where('transaction_type', 'cash_out')
                ->orderBy('transaction_date', 'desc');
        }])
        ->get();

    $agingData = [];
    $totals = [
        'current' => 0,
        '1-30' => 0,
        '31-60' => 0,
        '61-90' => 0,
        'over_90' => 0,
        'total' => 0,
    ];

    foreach ($payees as $payee) {
        $ledgerBalance = $payee->payable_balance;
        if ($ledgerBalance <= 0.0001) {
            continue;
        }

        $payeeTotal = [
            'current' => 0,
            '1-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            'over_90' => 0,
            'total' => 0,
        ];

        $transactions = [];

        $openingBalance = (float) ($payee->opening_balance ?? 0);
        if ($openingBalance > 0.0001) {
            $openingDate = $payee->created_at ?? $referenceDate;
            $openingDaysPastDue = $openingDate->diffInDays($referenceDate);
            $openingRow = [
                'id' => null,
                'date' => $openingDate->format('Y-m-d'),
                'reference' => 'Opening Balance',
                'category' => 'Opening Balance',
                'amount' => $openingBalance,
                'days_past_due' => $openingDaysPastDue,
                'current' => 0,
                '1-30' => 0,
                '31-60' => 0,
                '61-90' => 0,
                'over_90' => 0,
            ];

            if ($openingDaysPastDue <= 0) {
                $openingRow['current'] = $openingBalance;
                $payeeTotal['current'] += $openingBalance;
            } elseif ($openingDaysPastDue <= 30) {
                $openingRow['1-30'] = $openingBalance;
                $payeeTotal['1-30'] += $openingBalance;
            } elseif ($openingDaysPastDue <= 60) {
                $openingRow['31-60'] = $openingBalance;
                $payeeTotal['31-60'] += $openingBalance;
            } elseif ($openingDaysPastDue <= 90) {
                $openingRow['61-90'] = $openingBalance;
                $payeeTotal['61-90'] += $openingBalance;
            } else {
                $openingRow['over_90'] = $openingBalance;
                $payeeTotal['over_90'] += $openingBalance;
            }

            $transactions[] = $openingRow;
        }

        foreach ($payee->transactions as $transaction) {
            $daysPastDue = $transaction->transaction_date->diffInDays($referenceDate);

            $transactionData = [
                'id' => $transaction->id,
                'date' => $transaction->transaction_date->format('Y-m-d'),
                'reference' => $transaction->reference_no,
                'category' => $transaction->category,
                'amount' => $transaction->amount,
                'days_past_due' => $daysPastDue,
                'current' => 0,
                '1-30' => 0,
                '31-60' => 0,
                '61-90' => 0,
                'over_90' => 0,
            ];

            if ($daysPastDue <= 0) {
                $transactionData['current'] = $transaction->amount;
                $payeeTotal['current'] += $transaction->amount;
            } elseif ($daysPastDue <= 30) {
                $transactionData['1-30'] = $transaction->amount;
                $payeeTotal['1-30'] += $transaction->amount;
            } elseif ($daysPastDue <= 60) {
                $transactionData['31-60'] = $transaction->amount;
                $payeeTotal['31-60'] += $transaction->amount;
            } elseif ($daysPastDue <= 90) {
                $transactionData['61-90'] = $transaction->amount;
                $payeeTotal['61-90'] += $transaction->amount;
            } else {
                $transactionData['over_90'] = $transaction->amount;
                $payeeTotal['over_90'] += $transaction->amount;
            }

            $transactions[] = $transactionData;
        }

        $payeeTotal['total'] = $payeeTotal['current'] + $payeeTotal['1-30'] +
            $payeeTotal['31-60'] + $payeeTotal['61-90'] + $payeeTotal['over_90'];

        if (count($transactions) > 0) {
            $agingData[] = [
                'payee' => $payee,
                'transactions' => $transactions,
                'totals' => $payeeTotal,
            ];

            $totals['current'] += $payeeTotal['current'];
            $totals['1-30'] += $payeeTotal['1-30'];
            $totals['31-60'] += $payeeTotal['31-60'];
            $totals['61-90'] += $payeeTotal['61-90'];
            $totals['over_90'] += $payeeTotal['over_90'];
            $totals['total'] += $payeeTotal['total'];
        }
    }

    return view('payables.reports.detailed-aging', compact('agingData', 'totals', 'referenceDate'));
}

    /**
     * Create a ledger account for the payee in Sundry Creditors group
     */
    protected function createPayeeLedgerAccount(Payee $payee): void
    {
        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        if (!$sundryCreditors) {
            Log::warning("Sundry Creditors account group not found. Skipping ledger creation for payee: {$payee->id}");
            return;
        }

        // Check if account already exists
        $existingAccount = Account::where('linkable_type', 'payee')
            ->where('linkable_id', $payee->id)
            ->first();

        if ($existingAccount) {
            return;
        }

        Account::create([
            'name' => $payee->name,
            'code' => 'PAY-' . str_pad($payee->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryCreditors->id,
            'account_type' => 'supplier',
            'opening_balance' => 0,
            'opening_balance_type' => 'credit',
            'current_balance' => 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'payee',
            'linkable_id' => $payee->id,
            'is_active' => true,
            'is_system' => false,
            'notes' => "Auto-created from payee ({$payee->type})",
        ]);
    }

    /**
     * Sync ledger account when payee is updated
     */
    protected function syncPayeeLedgerAccount(Payee $payee): void
    {
        $account = $payee->account_id ? Account::find($payee->account_id) : Account::where('linkable_type', 'payee')
            ->where('linkable_id', $payee->id)
            ->first();

        if (!$account) {
            // Create account if it doesn't exist
            $this->createPayeeLedgerAccount($payee);
            return;
        }

        $accountGroup = $this->payeeAccountService->getAccountGroupForCategory($payee->category ?: $payee->type ?: 'supplier');
        $accountType = $payee->category === 'supplier' ? 'supplier' : 'liability';

        // Update account name and group
        $account->update([
            'name' => $payee->name,
            'account_group_id' => $accountGroup ? $accountGroup->id : $account->account_group_id,
            'account_type' => $accountType,
        ]);
    }

    public function accrueInterest(Request $request, Payee $payee)
    {
        $asOf = $request->input('as_of_date') ? Carbon::parse($request->input('as_of_date')) : Carbon::today();
        if (!$payee->isCcLoan()) {
            return redirect()->back()->with('error', 'Interest accrual is only available for CC loans.');
        }

        $accrued = $this->payeeLoanService->accrueCcInterest($payee, $asOf, auth()->id());
        if (!$accrued) {
            return redirect()->back()->with('info', 'No interest to accrue for the selected date.');
        }

        return redirect()->back()->with('success', 'Interest accrued successfully.');
    }

    public function payInterest(Request $request, Payee $payee)
    {
        if (!$payee->isCcLoan()) {
            return redirect()->back()->with('error', 'Interest payment is only available for CC loans.');
        }

        $validated = $request->validate([
            'as_of_date' => 'nullable|date',
            'account_id' => 'nullable|exists:accounts,id',
            'rounding_rule' => 'nullable|in:nearest_100,nearest_10,none',
            'reference_no' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $asOf = $request->input('as_of_date') ? Carbon::parse($request->input('as_of_date')) : Carbon::today();
        $roundingRule = $validated['rounding_rule'] ?? 'nearest_100';

        DB::beginTransaction();
        try {
            $this->payeeLoanService->accrueCcInterest($payee, $asOf, auth()->id());
            $payee->refresh();

            $interestAccrued = (float) ($payee->interest_accrued ?? 0);
            if ($interestAccrued <= 0) {
                DB::rollBack();
                return redirect()->back()->with('info', 'No interest due to pay.');
            }

            $roundedInterest = $interestAccrued;
            if ($roundingRule === 'nearest_100') {
                $roundedInterest = round($interestAccrued / 100) * 100;
            } elseif ($roundingRule === 'nearest_10') {
                $roundedInterest = round($interestAccrued / 10) * 10;
            }
            $roundedInterest = max(0, $roundedInterest);
            if ($roundedInterest <= 0) {
                $roundedInterest = $interestAccrued;
            }
            $interestPayment = min($interestAccrued, $roundedInterest);
            $principalPayment = max(0, $roundedInterest - $interestPayment);

            $selectedAccount = null;
            if (!empty($validated['account_id'])) {
                $selectedAccount = Account::find($validated['account_id']);
            }

            if (!$selectedAccount) {
                $selectedAccount = Account::where('code', 'CASH-PRIMARY')->first();
            }

            if (!$selectedAccount) {
                $selectedAccount = Account::where('account_type', 'cash')->where('is_active', true)->orderBy('id')->first();
            }

            $paymentMethod = $selectedAccount?->account_type === 'bank' ? 'bank' : 'cash';
            $referenceNo = $validated['reference_no'] ?? null;
            if (!$referenceNo) {
                $referenceNo = 'INT-PAY-' . $payee->id . '-' . $asOf->format('Ymd') . '-' . now()->format('His');
            }
            $description = trim((string) ($validated['description'] ?? ''));
            if ($description === '') {
                $description = 'Interest payment (auto)';
            }

            $transaction = PayableTransaction::create([
                'payee_id' => $payee->id,
                'transaction_type' => 'cash_in',
                'payment_method' => $paymentMethod,
                'account_id' => $selectedAccount?->id,
                'reference_no' => $referenceNo,
                'amount' => $roundedInterest,
                'principal_amount' => $principalPayment,
                'interest_amount' => $interestPayment,
                'category' => 'interest_payment',
                'description' => $description,
                'transaction_date' => $asOf->toDateString(),
            ]);

            $payee->principal_balance = max(0, ($payee->principal_balance ?? 0) - $principalPayment);
            $payee->interest_accrued = max(0, ($payee->interest_accrued ?? 0) - $interestPayment);
            $payee->current_balance = ($payee->principal_balance ?? 0) + ($payee->interest_accrued ?? 0);
            $payee->save();

            try {
                $this->autoPostingService->postPayableTransaction($transaction);
            } catch (\Exception $e) {
                Log::warning("Failed to auto-post interest payment for payee {$payee->id}: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->back()->with('success', 'Interest payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error recording interest payment: ' . $e->getMessage());
        }
    }

    public function addKistiSkip(Request $request, Payee $payee)
    {
        if (!$payee->isDailyKisti()) {
            return redirect()->back()->with('error', 'This payee is not a daily kisti account.');
        }

        $validated = $request->validate([
            'skip_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        PayeeKistiSkip::updateOrCreate(
            ['payee_id' => $payee->id, 'skip_date' => $validated['skip_date']],
            ['reason' => $validated['reason'] ?? null]
        );

        return redirect()->back()->with('success', 'Skip day recorded successfully.');
    }

    protected function getPayeeCategories(): array
    {
        return [
            'supplier' => 'Supplier',
            'bank' => 'Bank',
            'personal' => 'Personal',
            'cc' => 'CC Loan',
            'sme' => 'SME Loan',
            'term_loan' => 'Term Loan',
            'daily_kisti' => 'Daily Kisti',
        ];
    }

    protected function getPayeeLedgerAccount(Payee $payee): ?Account
    {
        if ($payee->account_id) {
            return Account::find($payee->account_id);
        }

        return Account::where('linkable_type', 'payee')
            ->where('linkable_id', $payee->id)
            ->first();
    }

    protected function getAccountSignedBalance(?Account $account, string $type): float
    {
        if (!$account) {
            return 0.0;
        }

        if ($type === 'opening') {
            $amount = (float) ($account->opening_balance ?? 0);
            $balanceType = $account->opening_balance_type ?? 'credit';
        } elseif ($type === 'current') {
            $running = $account->running_balance;
            $amount = (float) ($running['balance'] ?? 0);
            $balanceType = $running['balance_type'] ?? ($account->current_balance_type ?? 'credit');
        } else {
            $amount = (float) ($account->current_balance ?? 0);
            $balanceType = $account->current_balance_type ?? 'credit';
        }

        return $balanceType === 'credit' ? $amount : -$amount;
    }

    protected function getLedgerStats(Payee $payee, ?Account $account): array
    {
        if (!$account) {
            return [
                'transaction_count' => $payee->transactions()->count(),
                'total_debit' => (float) $payee->transactions()->where('transaction_type', 'cash_in')->sum('amount'),
                'total_credit' => (float) $payee->transactions()->where('transaction_type', 'cash_out')->sum('amount'),
            ];
        }

        $entries = AccountEntry::query()
            ->where('account_id', $account->id);

        return [
            'transaction_count' => (int) $entries->count(),
            'total_debit' => (float) $entries->sum('debit_amount'),
            'total_credit' => (float) $entries->sum('credit_amount'),
        ];
    }

    protected function ledgerFromEntries(Request $request, Payee $payee, Account $account)
    {
        if ($request->ajax()) {
            $baseQuery = AccountEntry::query()
                ->where('account_entries.account_id', $account->id);

            $balanceMap = $this->buildEntryBalanceMap($account, clone $baseQuery);

            $query = $baseQuery->select([
                'account_entries.id as entry_id',
                'account_entries.entry_date as transaction_date',
                'account_entries.reference',
                'account_entries.source_type',
                'account_entries.source_id',
                'account_entries.description',
                'account_entries.debit_amount',
                'account_entries.credit_amount',
            ])
                ->orderBy('account_entries.entry_date', 'desc')
                ->orderBy('account_entries.id', 'desc');

            return DataTables::of($query)
                ->addColumn('reference_no', function ($row) {
                    if (!empty($row->reference)) {
                        return $row->reference;
                    }
                    return $row->source_type ? strtoupper($row->source_type) . '-' . $row->source_id : 'N/A';
                })
                ->addColumn('transaction_type', function ($row) {
                    return $row->debit_amount > 0 ? 'debit' : 'credit';
                })
                ->addColumn('amount', function ($row) {
                    return $row->debit_amount > 0 ? $row->debit_amount : $row->credit_amount;
                })
                ->addColumn('balance', function ($row) use ($balanceMap) {
                    $balance = $balanceMap[$row->entry_id] ?? 0;
                    return number_format($balance, 2, '.', '');
                })
                ->filterColumn('reference_no', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('account_entries.reference', 'like', '%' . $keyword . '%')
                            ->orWhere('account_entries.source_type', 'like', '%' . $keyword . '%')
                            ->orWhere('account_entries.source_id', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('description', function ($query, $keyword) {
                    $query->where('account_entries.description', 'like', '%' . $keyword . '%');
                })
                ->orderColumn('reference_no', 'account_entries.reference $1')
                ->orderColumn('description', 'account_entries.description $1')
                ->rawColumns(['transaction_type'])
                ->make(true);
        }

        $ledgerOpeningBalance = $this->getAccountSignedBalance($account, 'opening');
        $ledgerCurrentBalance = $this->getAccountSignedBalance($account, 'current');

        return view('payables.payees.ledger', compact('payee', 'ledgerOpeningBalance', 'ledgerCurrentBalance'));
    }

    protected function buildEntryBalanceMap(Account $account, $query): array
    {
        $openingBalance = $this->getAccountSignedBalance($account, 'opening');
        $balance = $openingBalance;

        $rows = $query
            ->orderBy('account_entries.entry_date', 'asc')
            ->orderBy('account_entries.id', 'asc')
            ->get([
                'account_entries.id',
                'account_entries.debit_amount',
                'account_entries.credit_amount',
            ]);

        $map = [];
        foreach ($rows as $row) {
            $balance += (float) ($row->credit_amount ?? 0) - (float) ($row->debit_amount ?? 0);
            $map[$row->id] = $balance;
        }

        return $map;
    }

    protected function printLedgerFromEntries(Payee $payee, Request $request, Account $account)
    {
        $startDate = $request->input('start_date') ? date('Y-m-d', strtotime($request->input('start_date'))) : null;
        $endDate = $request->input('end_date') ? date('Y-m-d', strtotime($request->input('end_date'))) : null;

        $query = AccountEntry::query()
            ->where('account_entries.account_id', $account->id);

        if ($startDate && $endDate) {
            $query->whereBetween('account_entries.entry_date', [$startDate, $endDate]);
        }

        $rows = $query
            ->orderBy('account_entries.entry_date', 'asc')
            ->orderBy('account_entries.id', 'asc')
            ->get([
                'account_entries.id',
                'account_entries.debit_amount',
                'account_entries.credit_amount',
                'account_entries.description',
                'account_entries.entry_date',
                'account_entries.reference',
                'account_entries.source_type',
                'account_entries.source_id',
            ]);

        $openingBalance = $this->getAccountSignedBalance($account, 'opening');
        $balance = $openingBalance;
        $transactions = collect();

        foreach ($rows as $row) {
            $isDebit = (float) $row->debit_amount > 0;
            $amount = $isDebit ? (float) $row->debit_amount : (float) $row->credit_amount;
            $balance += (float) $row->credit_amount - (float) $row->debit_amount;

            $reference = $row->reference ?: ($row->source_type ? strtoupper($row->source_type) . '-' . $row->source_id : 'N/A');

            $transactions->push((object) [
                'transaction_date' => Carbon::parse($row->entry_date),
                'reference_no' => $reference,
                'transaction_type' => $isDebit ? 'debit' : 'credit',
                'category' => $row->source_type,
                'description' => $row->description ?: 'N/A',
                'amount' => $amount,
                'running_balance' => $balance,
            ]);
        }

        $ledgerOpeningBalance = $openingBalance;
        $ledgerCurrentBalance = $this->getAccountSignedBalance($account, 'current');

        return view('payables.payees.print-ledger', compact(
            'payee',
            'transactions',
            'startDate',
            'endDate',
            'ledgerOpeningBalance',
            'ledgerCurrentBalance'
        ));
    }
}
