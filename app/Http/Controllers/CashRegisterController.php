<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CashRegisterTransaction;
use Yajra\DataTables\Facades\DataTables;

class CashRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:cash-register-list|cash-register-open|cash-register-close|cash-register-add-transaction', ['only' => ['index', 'show']]);
        $this->middleware('permission:cash-register-open', ['only' => ['open', 'store']]);
        $this->middleware('permission:cash-register-close', ['only' => ['close']]);
        $this->middleware('permission:cash-register-add-transaction', ['only' => ['addTransaction']]);
        $this->middleware('permission:cash-register-report', ['only' => ['report']]);
    }

    /**
     * Display a listing of cash registers
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = CashRegister::with('user')->latest('opened_at');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            return DataTables::of($query)
                ->addColumn('user_name', fn($r) => $r->user->name ?? 'N/A')
                ->editColumn('opened_at', fn($r) => $r->opened_at->format('d M Y, h:i A'))
                ->editColumn('closed_at', fn($r) => $r->closed_at ? $r->closed_at->format('d M Y, h:i A') : '-')
                ->addColumn('action', fn($r) => '')
                ->make(true);
        }

        $users = User::all();
        $myOpenRegister = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        return view('cash-registers.index', compact('users', 'myOpenRegister'));
    }

    /**
     * Show form for opening a new cash register
     */
    public function open()
    {
        // Check if user already has an open register
        $openRegister = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($openRegister) {
            return redirect()->route('cash-registers.show', $openRegister->id)
                ->with('info', 'You already have an open cash register.');
        }

        return view('cash-registers.open');
    }

    /**
     * Store a newly opened cash register
     */
    public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check again for open register
        $existing = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return redirect()->route('cash-registers.show', $existing->id)
                ->with('error', 'You already have an open cash register.');
        }

        DB::beginTransaction();
        try {
            $register = CashRegister::create([
                'user_id' => Auth::id(),
                'opening_balance' => $request->opening_balance,
                'expected_closing_balance' => $request->opening_balance,
                'opened_at' => now(),
                'status' => 'open',
                'opening_notes' => $request->notes,
                'terminal' => 'Terminal 1',
                'security_pin' => '',
            ]);

            // Log opening balance transaction
            CashRegisterTransaction::create([
                'cash_register_id' => $register->id,
                'transaction_type' => 'opening_balance',
                'payment_method' => 'cash',
                'amount' => $request->opening_balance,
                'notes' => 'Opening balance',
            ]);

            DB::commit();

            return redirect()->route('cash-registers.show', $register->id)
                ->with('success', 'Cash register opened successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to open cash register: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display a specific cash register
     */
    public function show(CashRegister $cashRegister)
    {
        $cashRegister->load(['transactions' => fn($q) => $q->latest(), 'user']);

        // Calculate totals
        $totals = [
            'deposits' => $cashRegister->transactions->where('transaction_type', 'deposit')->sum('amount'),
            'withdrawals' => $cashRegister->transactions->where('transaction_type', 'withdrawal')->sum('amount'),
            'sales' => $cashRegister->transactions->where('transaction_type', 'sale')->sum('amount'),
            'returns' => $cashRegister->transactions->where('transaction_type', 'return')->sum('amount'),
        ];

        return view('cash-registers.show', compact('cashRegister', 'totals'));
    }

    /**
     * Add a transaction to the register
     */
    public function addTransaction(Request $request, CashRegister $cashRegister)
    {
        if ($cashRegister->status !== 'open') {
            return back()->with('error', 'Cannot add transactions to a closed register.');
        }

        $request->validate([
            'transaction_type' => 'required|in:deposit,withdrawal,sale,return,expense',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $type = $request->transaction_type;
        $amount = $request->amount;

        // Check if withdrawal is allowed
        if (in_array($type, ['withdrawal', 'return', 'expense'])) {
            if ($amount > $cashRegister->expected_closing_balance) {
                return back()->with('error', 'Insufficient balance for this transaction.');
            }
        }

        DB::beginTransaction();
        try {
            CashRegisterTransaction::create([
                'cash_register_id' => $cashRegister->id,
                'transaction_type' => $type,
                'payment_method' => 'cash',
                'amount' => $amount,
                'notes' => $request->notes,
            ]);

            // Update expected balance
            if (in_array($type, ['deposit', 'sale'])) {
                $cashRegister->expected_closing_balance += $amount;
            } else {
                $cashRegister->expected_closing_balance -= $amount;
            }
            $cashRegister->save();

            DB::commit();

            $typeLabels = [
                'deposit' => 'Cash deposited',
                'withdrawal' => 'Cash withdrawn',
                'sale' => 'Sale recorded',
                'return' => 'Return recorded',
                'expense' => 'Expense recorded',
            ];

            return back()->with('success', $typeLabels[$type] . ' successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    /**
     * Close the cash register
     */
    public function close(Request $request, CashRegister $cashRegister)
    {
        if ($cashRegister->status !== 'open') {
            return back()->with('error', 'This register is already closed.');
        }

        $request->validate([
            'actual_closing_balance' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $variance = $request->actual_closing_balance - $cashRegister->expected_closing_balance;

            $cashRegister->update([
                'actual_closing_balance' => $request->actual_closing_balance,
                'variance' => $variance,
                'closing_notes' => $request->closing_notes,
                'closed_at' => now(),
                'status' => 'closed',
            ]);

            // Log closing balance transaction
            CashRegisterTransaction::create([
                'cash_register_id' => $cashRegister->id,
                'transaction_type' => 'closing_balance',
                'payment_method' => 'cash',
                'amount' => $request->actual_closing_balance,
                'notes' => 'Closing balance. Variance: ৳' . number_format($variance, 2),
            ]);

            DB::commit();

            $message = 'Cash register closed successfully!';
            if ($variance != 0) {
                $message .= ' Variance: ৳' . number_format(abs($variance), 2) . ($variance > 0 ? ' (Over)' : ' (Short)');
            }

            return redirect()->route('cash-registers.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to close register: ' . $e->getMessage());
        }
    }

    /**
     * Cash register report
     */
    public function report(Request $request)
    {
        $query = CashRegister::with('user')->where('status', 'closed');

        if ($request->filled('date_from')) {
            $query->whereDate('closed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('closed_at', '<=', $request->date_to);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $registers = $query->latest('closed_at')->paginate(20);
        $users = User::all();

        // Summary
        $summary = [
            'total_opening' => $registers->sum('opening_balance'),
            'total_closing' => $registers->sum('actual_closing_balance'),
            'total_variance' => $registers->sum('variance'),
            'count' => $registers->total(),
        ];

        return view('cash-registers.report', compact('registers', 'users', 'summary'));
    }

    /**
     * Delete a cash register (admin only)
     */
    public function destroy(CashRegister $cashRegister)
    {
        if ($cashRegister->status === 'open') {
            return back()->with('error', 'Cannot delete an open register. Close it first.');
        }

        try {
            $cashRegister->transactions()->delete();
            $cashRegister->delete();

            return redirect()->route('cash-registers.index')
                ->with('success', 'Cash register deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
