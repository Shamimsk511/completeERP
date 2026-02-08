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
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->string('security_pin')->after('status')->comment('Hashed 4-digit PIN for register access');
            $table->softDeletes();
            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['status', 'opened_at']);
            $table->index(['opened_at', 'closed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
             $table->dropColumn('security_pin');
            $table->softDeletes();
        });
    }
};
