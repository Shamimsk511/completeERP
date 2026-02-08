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
        Schema::create('call_logs', function (Blueprint $table) {
 $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('call_status', ['successful', 'missed', 'busy', 'disconnected']);
            $table->unsignedInteger('duration')->nullable(); // in minutes
            $table->text('notes')->nullable();
            $table->date('payment_promise_date')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->enum('outcome', ['payment_promised', 'payment_made', 'dispute', 'no_response', 'other'])->nullable();
            $table->timestamp('called_at');
            $table->timestamps();
            
            // Indexes for reporting and performance
            $table->index(['customer_id', 'called_at']);
            $table->index(['call_status']);
            $table->index(['user_id', 'called_at']);
            $table->index(['called_at']);
            $table->index(['payment_promise_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
