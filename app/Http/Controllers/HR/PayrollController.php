<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAdvanceDeduction;
use App\Models\EmployeeAdjustment;
use App\Models\EmployeeAttendance;
use App\Models\EmployeePayroll;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountEntry;
use App\Services\Accounting\GeneralLedgerService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:employee-payroll');
    }

    public function index(Request $request)
    {
        $periodStart = $request->get('period_start', now()->startOfMonth()->toDateString());
        $periodEnd = $request->get('period_end', now()->endOfMonth()->toDateString());
        $hasFilter = $request->filled('period_start') || $request->filled('period_end');

        $query = EmployeePayroll::with('employee')->orderBy('period_end', 'desc');

        if ($hasFilter) {
            $query->where('period_start', $periodStart)
                ->where('period_end', $periodEnd);
        }

        $payrolls = $query->get();

        return view('hr.payrolls.index', compact('payrolls', 'periodStart', 'periodEnd', 'hasFilter'));
    }

    public function run(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'deduct_advances' => 'nullable|boolean',
        ]);

        $periodStart = $validated['period_start'];
        $periodEnd = $validated['period_end'];
        $deductAdvances = $request->has('deduct_advances');

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $calculated = $this->calculatePayroll($employee, $periodStart, $periodEnd, $deductAdvances);

                $payroll = EmployeePayroll::firstOrNew([
                    'employee_id' => $employee->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                ]);

                if ($payroll->exists && $payroll->status === 'paid') {
                    continue;
                }

                $payroll->fill(array_merge($calculated, [
                    'employee_id' => $employee->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'basic_salary' => $employee->basic_salary ?? 0,
                    'created_by' => auth()->id(),
                ]));

                $payroll->status = $payroll->status ?: 'draft';
                $payroll->save();

                if ($payroll->gross_salary > 0) {
                    $this->ensurePayrollAccrualEntries($payroll);
                }
            }

            DB::commit();

            return redirect()->route('hr.payrolls.index', [
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ])->with('success', 'Payroll generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Payroll generation failed: ' . $e->getMessage());
        }
    }

    public function show(EmployeePayroll $payroll)
    {
        $payroll->load('employee');
        $cashAccounts = Account::whereIn('account_type', ['cash', 'bank'])->where('is_active', true)->orderBy('name')->get();

        return view('hr.payrolls.show', compact('payroll', 'cashAccounts'));
    }

    public function print(EmployeePayroll $payroll)
    {
        $payroll->load('employee');

        return view('hr.payrolls.print', compact('payroll'));
    }

    public function markPaid(Request $request, EmployeePayroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Payroll already marked as paid.');
        }

        $validated = $request->validate([
            'paid_at' => 'required|date',
            'cash_account_id' => 'required|exists:accounts,id',
        ]);

        DB::beginTransaction();
        try {
            if ($payroll->gross_salary > 0) {
                $this->ensurePayrollAccrualEntries($payroll);
            }

            $this->createPayrollPaymentEntries($payroll, $validated['cash_account_id'], $validated['paid_at']);

            $payroll->update([
                'status' => 'paid',
                'paid_at' => Carbon::parse($validated['paid_at']),
                'cash_account_id' => $validated['cash_account_id'],
            ]);

            $this->applyAdvanceDeductions($payroll);

            DB::commit();

            return redirect()->route('hr.payrolls.show', $payroll)
                ->with('success', 'Payroll marked as paid successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to mark payroll as paid: ' . $e->getMessage());
        }
    }

    protected function calculatePayroll(Employee $employee, string $periodStart, string $periodEnd, bool $deductAdvances): array
    {
        $basicSalary = $employee->basic_salary ?? 0;
        $weekendDays = $this->countWeekendDays($periodStart, $periodEnd);
        $totalDays = Carbon::parse($periodStart)->diffInDays(Carbon::parse($periodEnd)) + 1;
        $workingDays = max(0, $totalDays - $weekendDays);

        $attendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get()
            ->groupBy('status');

        $absentDays = $attendance->get('absent', collect())->count();
        $paidAbsentDays = $attendance->get('paid_absent', collect())->count();

        $presentDays = max(0, $workingDays - $absentDays - $paidAbsentDays);
        $perDaySalary = $workingDays > 0 ? round($basicSalary / $workingDays, 2) : 0;
        $absentDeduction = round($perDaySalary * $absentDays, 2);

        $adjustments = EmployeeAdjustment::where('employee_id', $employee->id)
            ->whereBetween('effective_date', [$periodStart, $periodEnd])
            ->get();

        $bonusAmount = $adjustments->where('type', 'bonus')->sum('amount');
        $otherBonusAmount = $adjustments->whereIn('type', ['other_bonus', 'allowance'])->sum('amount');
        $incrementAmount = $adjustments->where('type', 'increment')->sum('amount');
        $adjustmentDeduction = $adjustments->where('type', 'deduction')->sum('amount');

        $deductionAmount = round($absentDeduction + $adjustmentDeduction, 2);
        $grossSalary = round($basicSalary + $bonusAmount + $otherBonusAmount + $incrementAmount, 2);

        $advanceDeduction = 0;
        if ($deductAdvances) {
            $advanceDeduction = $this->calculateAdvanceDeduction($employee, $periodEnd, $grossSalary - $deductionAmount);
        }

        $netPay = round($grossSalary - $deductionAmount - $advanceDeduction, 2);

        return [
            'per_day_salary' => $perDaySalary,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'paid_absent_days' => $paidAbsentDays,
            'weekend_days' => $weekendDays,
            'deduction_amount' => $deductionAmount,
            'bonus_amount' => $bonusAmount,
            'other_bonus_amount' => $otherBonusAmount,
            'increment_amount' => $incrementAmount,
            'advance_deduction' => $advanceDeduction,
            'gross_salary' => $grossSalary,
            'net_pay' => $netPay,
        ];
    }

    protected function countWeekendDays(string $start, string $end): int
    {
        $settings = BusinessSetting::first();
        $weekendDaysRaw = $settings?->weekend_days;
        if (is_array($weekendDaysRaw)) {
            $weekendDays = $weekendDaysRaw;
        } elseif ($weekendDaysRaw) {
            $weekendDays = json_decode($weekendDaysRaw, true) ?: ['Friday'];
        } else {
            $weekendDays = ['Friday'];
        }
        $weekendDays = array_map('strtolower', $weekendDays ?? []);

        $period = CarbonPeriod::create($start, $end);
        $count = 0;

        foreach ($period as $date) {
            if (in_array(strtolower($date->format('l')), $weekendDays, true)) {
                $count++;
            }
        }

        return $count;
    }

    protected function calculateAdvanceDeduction(Employee $employee, string $periodEnd, float $maxDeduct): float
    {
        if ($maxDeduct <= 0) {
            return 0;
        }

        $advances = EmployeeAdvance::where('employee_id', $employee->id)
            ->where('status', 'open')
            ->whereDate('date', '<=', $periodEnd)
            ->get();

        $outstanding = $advances->sum(fn($advance) => $advance->outstanding_amount);

        return round(min($outstanding, $maxDeduct), 2);
    }

    protected function applyAdvanceDeductions(EmployeePayroll $payroll): void
    {
        if ($payroll->advance_deduction <= 0) {
            return;
        }

        $remaining = $payroll->advance_deduction;

        $advances = EmployeeAdvance::where('employee_id', $payroll->employee_id)
            ->where('status', 'open')
            ->orderBy('date')
            ->get();

        foreach ($advances as $advance) {
            if ($remaining <= 0) {
                break;
            }

            $outstanding = $advance->outstanding_amount;
            if ($outstanding <= 0) {
                continue;
            }

            $deduct = min($outstanding, $remaining);

            EmployeeAdvanceDeduction::create([
                'employee_advance_id' => $advance->id,
                'employee_payroll_id' => $payroll->id,
                'amount' => $deduct,
            ]);

            $remaining -= $deduct;

            if ($advance->outstanding_amount <= 0.01) {
                $advance->update(['status' => 'settled']);
            }
        }
    }

    protected function ensurePayrollAccrualEntries(EmployeePayroll $payroll): void
    {
        $exists = AccountEntry::where('source_type', 'payroll_accrual')
            ->where('source_id', $payroll->id)
            ->exists();

        if ($exists) {
            return;
        }

        $this->createPayrollAccrualEntries($payroll);
    }

    protected function createPayrollAccrualEntries(EmployeePayroll $payroll): void
    {
        $employeeAccount = $this->getEmployeeAccount($payroll->employee_id);
        $salaryExpense = Account::where('code', 'SALARY-EXPENSE')->first();

        if (!$salaryExpense) {
            throw new \Exception('Salary Expense account not found. Please seed chart of accounts.');
        }

        $entryDate = $payroll->period_end;
        $reference = 'PAYROLL-' . $payroll->id;

        $this->glService->replaceEntries('payroll_accrual', $payroll->id, $entryDate, [
            [
                'account_id' => $salaryExpense->id,
                'debit_amount' => $payroll->gross_salary,
                'credit_amount' => 0,
                'reference' => $reference,
                'description' => 'Salary expense',
            ],
            [
                'account_id' => $employeeAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $payroll->gross_salary,
                'reference' => $reference,
                'description' => 'Salary payable',
            ],
        ]);
    }

    protected function createPayrollPaymentEntries(EmployeePayroll $payroll, int $cashAccountId, string $paidAt): void
    {
        $employeeAccount = $this->getEmployeeAccount($payroll->employee_id);
        $cashAccount = Account::findOrFail($cashAccountId);

        $amount = $payroll->net_pay;

        if ($amount <= 0) {
            throw new \Exception('Net pay must be greater than zero to mark as paid.');
        }

        $entryDate = Carbon::parse($paidAt)->toDateString();
        $reference = 'PAYROLL-PAY-' . $payroll->id;

        $this->glService->replaceEntries('payroll_payment', $payroll->id, $entryDate, [
            [
                'account_id' => $employeeAccount->id,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'reference' => $reference,
                'description' => 'Salary paid',
            ],
            [
                'account_id' => $cashAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'reference' => $reference,
                'description' => 'Cash/Bank paid',
            ],
        ]);
    }

    protected function getEmployeeAccount(int $employeeId): Account
    {
        $account = Account::where('linkable_type', 'employee')
            ->where('linkable_id', $employeeId)
            ->first();

        if ($account) {
            return $account;
        }

        $employee = Employee::findOrFail($employeeId);
        $sundryCreditors = \App\Models\Accounting\AccountGroup::where('code', 'SUNDRY-CREDITORS')->first();

        return Account::create([
            'name' => $employee->name,
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
            'notes' => 'Auto-created from payroll',
        ]);
    }
}
