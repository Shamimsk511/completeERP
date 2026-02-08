<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('default_godown_id')->nullable()->after('category_id')->constrained('godowns')->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('godown_id')->nullable()->after('company_id')->constrained('godowns')->nullOnDelete();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('godown_id')->nullable()->after('product_id')->constrained('godowns')->nullOnDelete();
        });

        Schema::table('challan_items', function (Blueprint $table) {
            $table->foreignId('godown_id')->nullable()->after('product_id')->constrained('godowns')->nullOnDelete();
        });

        Schema::table('other_delivery_items', function (Blueprint $table) {
            $table->foreignId('godown_id')->nullable()->after('product_id')->constrained('godowns')->nullOnDelete();
        });

        Schema::table('other_delivery_return_items', function (Blueprint $table) {
            $table->foreignId('godown_id')->nullable()->after('product_id')->constrained('godowns')->nullOnDelete();
        });

        Schema::table('product_return_items', function (Blueprint $table) {
            $table->foreignId('godown_id')->nullable()->after('product_id')->constrained('godowns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_return_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('godown_id');
        });

        Schema::table('other_delivery_return_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('godown_id');
        });

        Schema::table('other_delivery_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('godown_id');
        });

        Schema::table('challan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('godown_id');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('godown_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('godown_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_godown_id');
        });
    }
};
