<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['invoice_type', 'invoice_date']);
            $table->index('referrer_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('purchase_date');
            $table->index(['company_id', 'purchase_date']);
        });

        Schema::table('product_returns', function (Blueprint $table) {
            $table->index('return_date');
            $table->index(['customer_id', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['invoice_type', 'invoice_date']);
            $table->dropIndex(['referrer_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['purchase_date']);
            $table->dropIndex(['company_id', 'purchase_date']);
        });

        Schema::table('product_returns', function (Blueprint $table) {
            $table->dropIndex(['return_date']);
            $table->dropIndex(['customer_id', 'return_date']);
        });
    }
};
