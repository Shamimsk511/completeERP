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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'weight_value')) {
                $table->decimal('weight_value', 10, 3)->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'weight_unit')) {
                $table->string('weight_unit', 20)->nullable()->after('weight_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'weight_value')) {
                $table->dropColumn('weight_value');
            }
            if (Schema::hasColumn('products', 'weight_unit')) {
                $table->dropColumn('weight_unit');
            }
        });
    }
};
