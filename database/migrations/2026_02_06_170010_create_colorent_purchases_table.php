<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colorent_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('payee_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date')->index();
            $table->string('reference_no', 50)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['payee_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colorent_purchases');
    }
};
