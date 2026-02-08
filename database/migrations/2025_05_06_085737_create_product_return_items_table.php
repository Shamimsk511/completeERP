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
        Schema::create('product_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('product_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items');
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->integer('boxes')->nullable();
            $table->integer('pieces')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_return_items');
    }
};
