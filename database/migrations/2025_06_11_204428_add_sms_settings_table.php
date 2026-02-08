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
        if (!Schema::hasTable('sms_settings')) {
            Schema::create('sms_settings', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->default('bdbulksms'); // bdbulksms, greenweb, ssl
                $table->string('provider_name')->default('BD Bulk SMS');
                $table->text('api_token')->nullable();
                $table->string('api_url')->default('https://api.bdbulksms.net/g_api.php');
                $table->string('sender_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('sms_enabled')->default(true);
                $table->json('settings')->nullable(); // Additional provider-specific settings
                $table->decimal('balance', 10, 2)->default(0);
                $table->integer('total_sent')->default(0);
                $table->integer('monthly_sent')->default(0);
                $table->timestamp('last_balance_check')->nullable();
                $table->timestamp('expiry_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('provider');
                $table->string('phone');
                $table->text('message');
                $table->string('status'); // sent, failed, pending
                $table->text('response')->nullable();
                $table->string('reference_id')->nullable();
                $table->decimal('cost', 8, 4)->default(0);
                $table->morphs('sendable'); // For polymorphic relationship (customer, transaction, etc.)
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();

                $table->index(['provider', 'status']);
                $table->index(['phone', 'created_at']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_settings');
    }
};