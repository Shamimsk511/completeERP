<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'sales_account_id')) {
                $table->foreignId('sales_account_id')
                    ->nullable()
                    ->constrained('accounts')
                    ->nullOnDelete()
                    ->after('invoice_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sales_account_id')) {
                $table->dropForeign(['sales_account_id']);
                $table->dropColumn('sales_account_id');
            }
        });
    }
};
