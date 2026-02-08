<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:employee-list|employee-create|employee-edit|employee-delete|employee-ledger', ['only' => ['index', 'show', 'ledger']]);
        $this->middleware('permission:employee-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:employee-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:employee-delete', ['only' => ['destroy']]);
        $this->middleware('permission:employee-ledger', ['only' => ['ledger', 'printLedger']]);
    }

    public function index()
    {
        $employees = Employee::with('user')->orderBy('name')->paginate(25);

        return view('hr.employees.index', compact('employees'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('hr.employees.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'nid' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
            'photo' => 'nullable|image|max:2048',
            'document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('document')) {
            $validated['file_path'] = $request->file('document')->store('employees/files', 'public');
        }

        DB::beginTransaction();
        try {
            $employee = Employee::create($validated);
            $this->createEmployeeLedgerAccount($employee);

            DB::commit();

            return redirect()->route('hr.employees.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'payrolls', 'advances', 'attendances']);

        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $users = User::orderBy('name')->get();

        return view('hr.employees.edit', compact('employee', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'nid' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
            'photo' => 'nullable|image|max:2048',
            'document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('document')) {
            if ($employee->file_path) {
                Storage::disk('public')->delete($employee->file_path);
            }
            $validated['file_path'] = $request->file('document')->store('employees/files', 'public');
        }

        DB::beginTransaction();
        try {
            $employee->update($validated);
            $this->syncEmployeeLedgerAccount($employee);

            DB::commit();

            return redirect()->route('hr.employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        if ($employee->payrolls()->exists() || $employee->advances()->exists() || $employee->attendances()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete employee with payrolls, advances, or attendance records.',
            ], 422);
        }

        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }
        if ($employee->file_path) {
            Storage::disk('public')->delete($employee->file_path);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully.',
        ]);
    }

    public function ledger(Request $request, Employee $employee)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());

        $account = $this->getOrCreateEmployeeAccount($employee);
        $ledger = $this->glService->getAccountLedger($account, $fromDate, $toDate);

        return view('hr.employees.ledger', compact('employee', 'ledger', 'fromDate', 'toDate'));
    }

    public function printLedger(Request $request, Employee $employee)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());

        $account = $this->getOrCreateEmployeeAccount($employee);
        $ledger = $this->glService->getAccountLedger($account, $fromDate, $toDate);

        return view('hr.employees.ledger-print', compact('employee', 'ledger', 'fromDate', 'toDate'));
    }

    protected function getOrCreateEmployeeAccount(Employee $employee): Account
    {
        $account = Account::where('linkable_type', 'employee')
            ->where('linkable_id', $employee->id)
            ->first();

        if ($account) {
            return $account;
        }

        return $this->createEmployeeLedgerAccount($employee);
    }

    protected function createEmployeeLedgerAccount(Employee $employee): Account
    {
        $sundryCreditors = AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        if (!$sundryCreditors) {
            Log::warning("Sundry Creditors group missing. Employee account not created: {$employee->id}");
        }

        return Account::create([
            'name' => $employee->name,
            'code' => 'EMP-' . str_pad($employee->id, 5, '0', STR_PAD_LEFT),
            'account_group_id' => $sundryCreditors?->id,
            'account_type' => 'employee',
            'opening_balance' => 0,
            'opening_balance_type' => 'credit',
            'current_balance' => 0,
            'current_balance_type' => 'credit',
            'linkable_type' => 'employee',
            'linkable_id' => $employee->id,
            'is_active' => true,
            'is_system' => false,
            'notes' => 'Auto-created from employee',
        ]);
    }

    protected function syncEmployeeLedgerAccount(Employee $employee): void
    {
        $account = Account::where('linkable_type', 'employee')
            ->where('linkable_id', $employee->id)
            ->first();

        if (!$account) {
            $this->createEmployeeLedgerAccount($employee);
            return;
        }

        $account->update(['name' => $employee->name]);
    }
}
