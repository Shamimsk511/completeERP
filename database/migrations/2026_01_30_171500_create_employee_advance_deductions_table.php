<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_advance_deductions')) {
            Schema::create('employee_advance_deductions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_advance_id')->constrained('employee_advances')->cascadeOnDelete();
                $table->foreignId('employee_payroll_id')->constrained('employee_payrolls')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->timestamps();

                $table->index(['employee_advance_id', 'employee_payroll_id'], 'emp_adv_pay_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advance_deductions');
    }
};
