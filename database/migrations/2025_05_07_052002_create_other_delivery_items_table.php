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
        Schema::create('other_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('other_delivery_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->integer('cartons')->nullable();
            $table->integer('pieces')->nullable();
            $table->timestamps();
            
            $table->foreign('other_delivery_id')->references('id')->on('other_deliveries')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_delivery_items');
    }
};
