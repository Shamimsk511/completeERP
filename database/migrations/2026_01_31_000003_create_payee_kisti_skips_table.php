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
        Schema::create('payee_kisti_skips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payee_id')->constrained()->onDelete('cascade');
            $table->date('skip_date');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['payee_id', 'skip_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payee_kisti_skips');
    }
};
