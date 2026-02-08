<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherDeliveryReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_delivery_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('other_delivery_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->integer('cartons')->nullable();
            $table->integer('pieces')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('other_delivery_return_items');
    }
}
