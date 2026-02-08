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
        Schema::table('cash_register_transactions', function (Blueprint $table) {
            // Add reference_number if not exists
            if (!Schema::hasColumn('cash_register_transactions', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('amount');
            }

            // Add created_by if not exists
            if (!Schema::hasColumn('cash_register_transactions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            }

            // Add soft deletes if not exists
            if (!Schema::hasColumn('cash_register_transactions', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_register_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_register_transactions', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('cash_register_transactions', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('cash_register_transactions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
