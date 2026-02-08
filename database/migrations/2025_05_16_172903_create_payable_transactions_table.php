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
        Schema::create('payable_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payee_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // cash_in, cash_out
            $table->string('payment_method')->nullable(); // cash, bank, etc.
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable(); // payment, commission, purchase, borrow, etc.
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_transactions');
    }
};
