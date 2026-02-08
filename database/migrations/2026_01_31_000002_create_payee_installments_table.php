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
        Schema::create('payee_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payee_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('principal_due', 15, 2)->default(0);
            $table->decimal('interest_due', 15, 2)->default(0);
            $table->decimal('total_due', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, waived
            $table->date('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['payee_id', 'installment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payee_installments');
    }
};
