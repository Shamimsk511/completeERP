<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->foreignId('parent_id')->nullable()->constrained('account_groups')->onDelete('restrict');
            $table->enum('nature', ['assets', 'liabilities', 'income', 'expenses', 'capital']);
            $table->enum('affects_gross_profit', ['yes', 'no'])->default('no');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nature', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_groups');
    }
};
