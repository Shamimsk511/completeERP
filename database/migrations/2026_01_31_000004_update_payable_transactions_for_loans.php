<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payable_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payable_transactions', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('payment_method')->constrained('accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('payable_transactions', 'principal_amount')) {
                $table->decimal('principal_amount', 15, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('payable_transactions', 'interest_amount')) {
                $table->decimal('interest_amount', 15, 2)->default(0)->after('principal_amount');
            }
            if (!Schema::hasColumn('payable_transactions', 'kisti_days')) {
                $table->unsignedInteger('kisti_days')->nullable()->after('interest_amount');
            }
            if (!Schema::hasColumn('payable_transactions', 'installment_id')) {
                $table->unsignedBigInteger('installment_id')->nullable()->after('kisti_days');
                $table->foreign('installment_id')->references('id')->on('payee_installments')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payable_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payable_transactions', 'installment_id')) {
                $table->dropForeign(['installment_id']);
                $table->dropColumn('installment_id');
            }
            if (Schema::hasColumn('payable_transactions', 'kisti_days')) {
                $table->dropColumn('kisti_days');
            }
            if (Schema::hasColumn('payable_transactions', 'interest_amount')) {
                $table->dropColumn('interest_amount');
            }
            if (Schema::hasColumn('payable_transactions', 'principal_amount')) {
                $table->dropColumn('principal_amount');
            }
            if (Schema::hasColumn('payable_transactions', 'account_id')) {
                $table->dropConstrainedForeignId('account_id');
            }
        });
    }
};
