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
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->id();
    $table->unsignedBigInteger('cash_register_id');
    $table->unsignedBigInteger('transaction_id')->nullable();
    $table->string('transaction_type'); // sale, return, expense, deposit, withdrawal
    $table->string('payment_method');
    $table->decimal('amount', 15, 2);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->foreign('cash_register_id')->references('id')->on('cash_registers');
    $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_transactions');
    }
};
