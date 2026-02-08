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
        Schema::create('cash_registers', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->decimal('opening_balance', 15, 2);
    $table->decimal('expected_closing_balance', 15, 2)->nullable();
    $table->decimal('actual_closing_balance', 15, 2)->nullable();
    $table->decimal('variance', 15, 2)->nullable();
    $table->timestamp('opened_at');
    $table->timestamp('closed_at')->nullable();
    $table->text('closing_notes')->nullable();
    $table->enum('status', ['open', 'closed', 'suspended'])->default('open');
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
