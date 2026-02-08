<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\HR\EmployeeAdvanceController;
use App\Http\Controllers\HR\EmployeeAdjustmentController;

Route::prefix('hr')->name('hr.')->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/ledger', [EmployeeController::class, 'ledger'])->name('employees.ledger');
    Route::get('employees/{employee}/ledger/print', [EmployeeController::class, 'printLedger'])->name('employees.ledger.print');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::resource('advances', EmployeeAdvanceController::class)->except(['show']);
    Route::resource('adjustments', EmployeeAdjustmentController::class)->except(['show']);

    Route::get('payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
    Route::post('payrolls/run', [PayrollController::class, 'run'])->name('payrolls.run');
    Route::get('payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
    Route::post('payrolls/{payroll}/pay', [PayrollController::class, 'markPaid'])->name('payrolls.pay');
    Route::get('payrolls/{payroll}/print', [PayrollController::class, 'print'])->name('payrolls.print');
});
