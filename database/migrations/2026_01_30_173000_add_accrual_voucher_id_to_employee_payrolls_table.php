<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_payrolls', 'accrual_voucher_id')) {
                $table->foreignId('accrual_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete()->after('cash_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('employee_payrolls', 'accrual_voucher_id')) {
                $table->dropForeign(['accrual_voucher_id']);
                $table->dropColumn('accrual_voucher_id');
            }
        });
    }
};
