<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('restrict');

            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);

            $table->text('particulars')->nullable();
            $table->integer('line_order')->default(0);

            // Cost center support (for future use)
            $table->unsignedBigInteger('cost_center_id')->nullable();

            $table->timestamps();

            $table->index(['voucher_id', 'line_order']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_entries');
    }
};
