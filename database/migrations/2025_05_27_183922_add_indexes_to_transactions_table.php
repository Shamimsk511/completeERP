<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Indexes for debt collection reporting
            $table->index(['customer_id', 'type', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'type', 'created_at']);
            $table->dropIndex(['type', 'created_at']);
            $table->dropIndex(['created_at']);
        });
    }
};