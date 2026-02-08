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
        Schema::create('business_settings', function (Blueprint $table) {
        $table->id();
        $table->string('business_name');
        $table->string('email')->nullable();
        $table->string('phone');
        $table->string('address')->nullable();
        $table->string('bin_number')->nullable();
        $table->string('logo')->nullable();
        $table->text('bank_details')->nullable();
        $table->integer('return_policy_days')->default(90)->change;
        $table->text('return_policy_message')->nullable();
        $table->text('footer_message')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
