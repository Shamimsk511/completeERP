<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            if (!Schema::hasColumn('payees', 'category')) {
                $table->string('category')->default('supplier')->after('type');
            }
            if (!Schema::hasColumn('payees', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('category')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('payees', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('company_id')->constrained('accounts')->nullOnDelete();
            }

            if (!Schema::hasColumn('payees', 'principal_amount')) {
                $table->decimal('principal_amount', 15, 2)->default(0)->after('current_balance');
            }
            if (!Schema::hasColumn('payees', 'principal_balance')) {
                $table->decimal('principal_balance', 15, 2)->default(0)->after('principal_amount');
            }
            if (!Schema::hasColumn('payees', 'interest_rate')) {
                $table->decimal('interest_rate', 6, 2)->default(0)->after('principal_balance');
            }
            if (!Schema::hasColumn('payees', 'interest_accrued')) {
                $table->decimal('interest_accrued', 15, 2)->default(0)->after('interest_rate');
            }
            if (!Schema::hasColumn('payees', 'interest_last_accrual_date')) {
                $table->date('interest_last_accrual_date')->nullable()->after('interest_accrued');
            }
            if (!Schema::hasColumn('payees', 'loan_start_date')) {
                $table->date('loan_start_date')->nullable()->after('interest_last_accrual_date');
            }
            if (!Schema::hasColumn('payees', 'loan_term_months')) {
                $table->unsignedInteger('loan_term_months')->nullable()->after('loan_start_date');
            }
            if (!Schema::hasColumn('payees', 'installment_amount')) {
                $table->decimal('installment_amount', 15, 2)->default(0)->after('loan_term_months');
            }
            if (!Schema::hasColumn('payees', 'daily_kisti_amount')) {
                $table->decimal('daily_kisti_amount', 15, 2)->default(0)->after('installment_amount');
            }
            if (!Schema::hasColumn('payees', 'daily_kisti_start_date')) {
                $table->date('daily_kisti_start_date')->nullable()->after('daily_kisti_amount');
            }
        });

        if (Schema::hasColumn('payees', 'category')) {
            DB::table('payees')
                ->whereNull('category')
                ->update(['category' => DB::raw("CASE WHEN type IS NULL OR type = '' THEN 'supplier' ELSE type END")]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            if (Schema::hasColumn('payees', 'daily_kisti_start_date')) {
                $table->dropColumn('daily_kisti_start_date');
            }
            if (Schema::hasColumn('payees', 'daily_kisti_amount')) {
                $table->dropColumn('daily_kisti_amount');
            }
            if (Schema::hasColumn('payees', 'installment_amount')) {
                $table->dropColumn('installment_amount');
            }
            if (Schema::hasColumn('payees', 'loan_term_months')) {
                $table->dropColumn('loan_term_months');
            }
            if (Schema::hasColumn('payees', 'loan_start_date')) {
                $table->dropColumn('loan_start_date');
            }
            if (Schema::hasColumn('payees', 'interest_last_accrual_date')) {
                $table->dropColumn('interest_last_accrual_date');
            }
            if (Schema::hasColumn('payees', 'interest_accrued')) {
                $table->dropColumn('interest_accrued');
            }
            if (Schema::hasColumn('payees', 'interest_rate')) {
                $table->dropColumn('interest_rate');
            }
            if (Schema::hasColumn('payees', 'principal_balance')) {
                $table->dropColumn('principal_balance');
            }
            if (Schema::hasColumn('payees', 'principal_amount')) {
                $table->dropColumn('principal_amount');
            }
            if (Schema::hasColumn('payees', 'account_id')) {
                $table->dropConstrainedForeignId('account_id');
            }
            if (Schema::hasColumn('payees', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
            if (Schema::hasColumn('payees', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
