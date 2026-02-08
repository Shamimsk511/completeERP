<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:employee-attendance');
    }

    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $attendance = EmployeeAttendance::where('date', $date)
            ->get()
            ->keyBy('employee_id');

        return view('hr.attendance.index', compact('date', 'employees', 'attendance'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'nullable|in:present,absent,paid_absent',
            'notes' => 'nullable|array',
        ]);

        $date = $validated['date'];
        $statuses = $validated['status'];
        $notes = $validated['notes'] ?? [];

        DB::beginTransaction();
        try {
            foreach ($statuses as $employeeId => $status) {
                if (!$status) {
                    continue;
                }

                EmployeeAttendance::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $date],
                    [
                        'status' => $status,
                        'notes' => $notes[$employeeId] ?? null,
                        'marked_by' => auth()->id(),
                    ]
                );
            }

            DB::commit();

            return back()->with('success', 'Attendance saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }
}
