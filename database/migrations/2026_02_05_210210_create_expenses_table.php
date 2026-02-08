<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expenses')) {
            return;
        }

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->foreignId('expense_account_id')->constrained('accounts')->onDelete('restrict');
            $table->foreignId('payment_account_id')->constrained('accounts')->onDelete('restrict');
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            if (Schema::hasTable('tenants')) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete()->index();
            } else {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
            }
            $table->timestamps();

            $table->index(['expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
