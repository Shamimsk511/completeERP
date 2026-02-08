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
        Schema::create('debt_collection_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('due_date')->nullable();
            $table->timestamp('last_call_date')->nullable();
            $table->unsignedInteger('calls_made')->default(0);
            $table->unsignedInteger('missed_calls')->default(0);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->text('notes')->nullable();
            $table->date('payment_promise_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['due_date']);
            $table->index(['priority']);
            $table->index(['last_call_date']);
            $table->index(['customer_id', 'priority']);
            $table->index(['customer_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_collection_tracking');
    }
};
