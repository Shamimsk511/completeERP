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
        Schema::create('erp_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key', 100)->unique();
            $table->string('feature_name', 150);
            $table->string('feature_group', 100)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('config')->nullable(); // For additional feature-specific settings
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_feature_settings');
    }
};
