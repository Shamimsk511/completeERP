<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->enum('voucher_type', [
                'payment', 'receipt', 'contra', 'journal',
                'sales', 'purchase', 'debit_note', 'credit_note'
            ]);
            $table->date('voucher_date');

            // For auto-posted vouchers (linked to invoices, purchases, transactions)
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->string('reference_number')->nullable();
            $table->text('narration')->nullable();
            $table->decimal('total_amount', 15, 2);

            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->boolean('is_auto_posted')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['voucher_type', 'voucher_date']);
            $table->index(['source_type', 'source_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
