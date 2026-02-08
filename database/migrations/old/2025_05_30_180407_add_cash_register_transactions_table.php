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
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('transaction_type', [
                'opening_balance',
                'closing_balance', 
                'sale',
                'return',
                'expense',
                'deposit',
                'withdrawal',
                'suspension',
                'resumption',
                'void'
            ]);
            $table->enum('payment_method', [
                'cash',
                'bank',
                'mobile_bank',
                'cheque',
                'card',
                'system'
            ]);
            $table->decimal('amount', 10, 2);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();


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