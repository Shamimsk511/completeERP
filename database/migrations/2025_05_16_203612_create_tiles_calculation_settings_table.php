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
        Schema::create('tiles_calculation_settings', function (Blueprint $table) {
        $table->id();
    $table->unsignedBigInteger('tiles_category_id');
    $table->integer('light_times')->default(4);
    $table->integer('deco_times')->default(1);
    $table->integer('deep_times')->default(1);
    $table->timestamps();
    $table->foreign('tiles_category_id')->references('id')->on('tiles_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiles_calculation_settings');
    }
};
