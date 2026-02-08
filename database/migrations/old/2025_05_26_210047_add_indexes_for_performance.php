// Migration: add_indexes_for_performance.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['customer_id', 'invoice_date']);
            $table->index(['payment_status', 'invoice_date']);
            $table->index(['delivery_status', 'invoice_date']);
            $table->index(['invoice_date', 'created_at']);
            $table->index('invoice_number'); // for search
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('name'); // for search and dropdown
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'invoice_date']);
            $table->dropIndex(['payment_status', 'invoice_date']);
            $table->dropIndex(['delivery_status', 'invoice_date']);
            $table->dropIndex(['invoice_date', 'created_at']);
            $table->dropIndex(['invoice_number']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};