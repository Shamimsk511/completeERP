<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'weight_value')) {
                $table->decimal('weight_value', 10, 3)->nullable()->after('pieces_feet');
            }
            if (!Schema::hasColumn('categories', 'weight_unit')) {
                $table->string('weight_unit', 20)->nullable()->after('weight_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'weight_unit')) {
                $table->dropColumn('weight_unit');
            }
            if (Schema::hasColumn('categories', 'weight_value')) {
                $table->dropColumn('weight_value');
            }
        });
    }
};
