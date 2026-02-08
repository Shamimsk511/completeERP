<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->foreignId('account_group_id')->constrained()->onDelete('restrict');
            $table->enum('account_type', [
                'cash', 'bank', 'customer', 'supplier', 'expense',
                'income', 'asset', 'liability', 'capital', 'suspense'
            ]);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->enum('current_balance_type', ['debit', 'credit'])->default('debit');

            // Linkable to existing entities (customers, payees, companies)
            $table->string('linkable_type')->nullable();
            $table->unsignedBigInteger('linkable_id')->nullable();

            // Bank account details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code', 20)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_group_id', 'account_type']);
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
