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
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['tiles', 'other'])->default('tiles')->after('notes');
            $table->enum('delivery_status', ['pending', 'partial','delivered'])->default('pending')->after('invoice_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
             $table->dropColumn(['invoice_type', 'delivery_status']);
        });
    }
};
