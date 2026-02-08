<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'is_simple_product')) {
                $table->boolean('is_simple_product')->default(false)->after('name');
            }
            if (!Schema::hasColumn('categories', 'tile_width_in')) {
                $table->decimal('tile_width_in', 10, 2)->nullable()->after('is_simple_product');
            }
            if (!Schema::hasColumn('categories', 'tile_length_in')) {
                $table->decimal('tile_length_in', 10, 2)->nullable()->after('tile_width_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'tile_length_in')) {
                $table->dropColumn('tile_length_in');
            }
            if (Schema::hasColumn('categories', 'tile_width_in')) {
                $table->dropColumn('tile_width_in');
            }
            if (Schema::hasColumn('categories', 'is_simple_product')) {
                $table->dropColumn('is_simple_product');
            }
        });
    }
};
