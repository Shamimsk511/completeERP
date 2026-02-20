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
        $tables = [
            'customers',
            'transactions',
            'challans',
            'invoices',
            'products',
            'other_deliveries',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $foreignName = $tableName . '_deleted_by_foreign';
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                $table->index('deleted_by');
                $table->foreign('deleted_by', $foreignName)->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'customers',
            'transactions',
            'challans',
            'invoices',
            'products',
            'other_deliveries',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'deleted_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $foreignName = $tableName . '_deleted_by_foreign';
                $table->dropForeign($foreignName);
                $table->dropIndex(['deleted_by']);
                $table->dropColumn('deleted_by');
            });
        }
    }
};
