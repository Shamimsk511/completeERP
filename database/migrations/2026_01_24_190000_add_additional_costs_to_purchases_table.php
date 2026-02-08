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
        Schema::table('purchases', function (Blueprint $table) {
            // Additional cost fields
            if (!Schema::hasColumn('purchases', 'labour_cost')) {
                $table->decimal('labour_cost', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('purchases', 'transportation_cost')) {
                $table->decimal('transportation_cost', 15, 2)->default(0)->after('labour_cost');
            }
            if (!Schema::hasColumn('purchases', 'other_cost')) {
                $table->decimal('other_cost', 15, 2)->default(0)->after('transportation_cost');
            }
            if (!Schema::hasColumn('purchases', 'other_cost_description')) {
                $table->string('other_cost_description')->nullable()->after('other_cost');
            }
            // How to distribute costs: 'per_quantity' (proportional to qty), 'per_value' (proportional to value), 'equal' (equal per item)
            if (!Schema::hasColumn('purchases', 'cost_distribution_method')) {
                $table->enum('cost_distribution_method', ['per_quantity', 'per_value', 'equal'])->default('per_value')->after('other_cost_description');
            }
            // Whether to update product purchase prices with distributed costs
            if (!Schema::hasColumn('purchases', 'update_product_prices')) {
                $table->boolean('update_product_prices')->default(false)->after('cost_distribution_method');
            }
            // Grand total (total_amount + all additional costs)
            if (!Schema::hasColumn('purchases', 'grand_total')) {
                $table->decimal('grand_total', 15, 2)->default(0)->after('update_product_prices');
            }
        });

        // Add effective_purchase_price to purchase_items (price after cost distribution)
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'additional_cost')) {
                $table->decimal('additional_cost', 15, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('purchase_items', 'effective_price')) {
                $table->decimal('effective_price', 15, 2)->default(0)->after('additional_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $columns = ['labour_cost', 'transportation_cost', 'other_cost', 'other_cost_description', 'cost_distribution_method', 'update_product_prices', 'grand_total'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('purchases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'additional_cost')) {
                $table->dropColumn('additional_cost');
            }
            if (Schema::hasColumn('purchase_items', 'effective_price')) {
                $table->dropColumn('effective_price');
            }
        });
    }
};
