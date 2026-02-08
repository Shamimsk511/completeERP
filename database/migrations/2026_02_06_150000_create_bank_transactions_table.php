<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('bank_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('counter_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->date('transaction_date')->index();
            $table->enum('transaction_type', ['deposit', 'withdraw', 'adjustment']);
            $table->enum('direction', ['in', 'out'])->default('in');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
