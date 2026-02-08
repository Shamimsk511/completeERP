<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payable_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payable_transactions', 'skip_accounting')) {
                $table->boolean('skip_accounting')->default(false)->after('transaction_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payable_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payable_transactions', 'skip_accounting')) {
                $table->dropColumn('skip_accounting');
            }
        });
    }
};
