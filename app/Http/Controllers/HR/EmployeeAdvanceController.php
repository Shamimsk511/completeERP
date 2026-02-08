<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountEntry;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\AccountGroup;
use Illuminate\Support\Facades\Log;

class EmployeeAdvanceController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:employee-advance');
    }

    public function index()
    {
        $advances = EmployeeAdvance::with('employee')->orderBy('date', 'desc')->paginate(25);

        return view('hr.advances.index', compact('advances'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $cashAccounts = Account::whereIn('account_type', ['cash', 'bank'])->where('is_active', true)->orderBy('name')->get();

        return view('hr.advances.create', compact('employees', 'cashAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'cash_account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $advance = EmployeeAdvance::create([
                'employee_id' => $validated['employee_id'],
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $employeeAccount = $this->getOrCreateEmployeeAccount($advance->employee_id, $advance->employee->name);

            $cashAccount = Account::findOrFail($validated['cash_account_id']);

            $entryDate = $validated['date'];
            $reference = 'ADV-' . $advance->id;

            $this->glService->replaceEntries('employee_advance', $advance->id, $entryDate, [
                [
                    'account_id' => $employeeAccount->id,
                    'debit_amount' => $advance->amount,
                    'credit_amount' => 0,
                    'reference' => $reference,
                    'description' => 'Advance given',
                ],
                [
                    'account_id' => $cashAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $advance->amount,
                    'reference' => $reference,
                    'description' => 'Cash/Bank paid',
                ],
            ]);

            DB::commit();

            return redirect()->route('hr.advances.index')
                ->with('success', 'Advance recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to record advance: ' . $e->getMessage());
        }
    }

    public function edit(EmployeeAdvance $advance)
    {
        if ($this->hasPostedEntries($advance)) {
            return redirect()->route('hr.advances.index')->with('error', 'Posted advances cannot be edited.');
        }

        $employees = Employee::orderBy('name')->get();
        $cashAccounts = Account::whereIn('account_type', ['cash', 'bank'])->where('is_active', true)->orderBy('name')->get();

        return view('hr.advances.edit', compact('advance', 'employees', 'cashAccounts'));
    }

    public function update(Request $request, EmployeeAdvance $advance)
    {
        if ($this->hasPostedEntries($advance)) {
            return redirect()->route('hr.advances.index')->with('error', 'Posted advances cannot be edited.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|in:open,settled',
        ]);

        $advance->update($validated);

        return redirect()->route('hr.advances.index')
            ->with('success', 'Advance updated successfully.');
    }

    public function destroy(EmployeeAdvance $advance)
    {
        if ($this->hasPostedEntries($advance)) {
            return response()->json([
                'success' => false,
                'message' => 'Posted advances cannot be deleted.',
            ], 422);
        }

        $advance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advance deleted successfully.',
        ]);
    }

    protected function getOrCreateEmployeeAccount(int $employeeId, string $employeeName): Account
    {
        $account = Account::where('linkable_type', 'employee')
            ->where('linkable_id', $employeeId)
            ->first();

        if ($account) {
            return $account;
        }

        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();
        if (!$sundryCreditors) {
            Log::warning("Sundry Creditors group missing. Creating employee account without group: {$employeeId}");
        }

        return Account::create([
            'name' => $employeeName,
            'code' => 'EMP-' . str_pad($employeeId, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryCreditors?->id,
            'account_type' => 'employee',
            'opening_balance' => 0,
            'opening_balance_type' => 'credit',
            'current_balance' => 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'employee',
            'linkable_id' => $employeeId,
            'is_active' => true,
            'is_system' => false,
            'notes' => 'Auto-created from employee advance',
        ]);
    }

    protected function hasPostedEntries(EmployeeAdvance $advance): bool
    {
        return AccountEntry::where('source_type', 'employee_advance')
            ->where('source_id', $advance->id)
            ->exists();
    }
}
