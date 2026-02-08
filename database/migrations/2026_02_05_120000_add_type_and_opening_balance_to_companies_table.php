<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'type')) {
                $table->string('type', 20)->nullable()->after('contact');
            }
            if (!Schema::hasColumn('companies', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->nullable()->after('type');
            }
            if (!Schema::hasColumn('companies', 'opening_balance_type')) {
                $table->string('opening_balance_type', 10)->nullable()->after('opening_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'opening_balance_type')) {
                $table->dropColumn('opening_balance_type');
            }
            if (Schema::hasColumn('companies', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
            if (Schema::hasColumn('companies', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
