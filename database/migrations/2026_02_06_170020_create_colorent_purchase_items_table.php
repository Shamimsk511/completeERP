<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colorent_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('colorent_purchase_id')->constrained('colorent_purchases')->cascadeOnDelete();
            $table->foreignId('colorent_id')->constrained('colorents')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->boolean('update_price')->default(true);
            $table->timestamps();

            $table->index(['colorent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colorent_purchase_items');
    }
};
