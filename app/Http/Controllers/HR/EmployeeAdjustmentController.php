<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAdjustment;
use Illuminate\Http\Request;

class EmployeeAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:employee-adjustment');
    }

    public function index()
    {
        $adjustments = EmployeeAdjustment::with('employee')->orderBy('effective_date', 'desc')->paginate(25);

        return view('hr.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $types = $this->getAdjustmentTypes();

        return view('hr.adjustments.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        $typeKeys = array_keys($this->getAdjustmentTypes());
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:' . implode(',', $typeKeys),
            'amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        EmployeeAdjustment::create([
            'employee_id' => $validated['employee_id'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'effective_date' => $validated['effective_date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('hr.adjustments.index')
            ->with('success', 'Adjustment recorded successfully.');
    }

    public function edit(EmployeeAdjustment $adjustment)
    {
        $employees = Employee::orderBy('name')->get();
        $types = $this->getAdjustmentTypes();

        return view('hr.adjustments.edit', compact('adjustment', 'employees', 'types'));
    }

    public function update(Request $request, EmployeeAdjustment $adjustment)
    {
        $typeKeys = array_keys($this->getAdjustmentTypes());
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:' . implode(',', $typeKeys),
            'amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $adjustment->update($validated);

        return redirect()->route('hr.adjustments.index')
            ->with('success', 'Adjustment updated successfully.');
    }

    public function destroy(EmployeeAdjustment $adjustment)
    {
        $adjustment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Adjustment deleted successfully.',
        ]);
    }

    protected function getAdjustmentTypes(): array
    {
        return [
            'bonus' => 'Bonus',
            'other_bonus' => 'Other Bonus',
            'increment' => 'Increment',
            'deduction' => 'Deduction',
            'allowance' => 'Allowance',
        ];
    }
}
