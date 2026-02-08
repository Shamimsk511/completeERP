<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Fix all missing columns in cash register tables
     */
    public function up(): void
    {
        // Fix cash_registers table - add all potentially missing columns
        Schema::table('cash_registers', function (Blueprint $table) {
            // Add timestamps if missing
            if (!Schema::hasColumn('cash_registers', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('cash_registers', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            // Add security_pin if missing
            if (!Schema::hasColumn('cash_registers', 'security_pin')) {
                $table->string('security_pin')->nullable()->after('status');
            }

            // Add soft deletes if missing
            if (!Schema::hasColumn('cash_registers', 'deleted_at')) {
                $table->softDeletes();
            }

            // Add opening_notes if missing
            if (!Schema::hasColumn('cash_registers', 'opening_notes')) {
                $table->text('opening_notes')->nullable();
            }

            // Add terminal if missing
            if (!Schema::hasColumn('cash_registers', 'terminal')) {
                $table->string('terminal')->default('Terminal 1');
            }

            // Add suspended_at if missing
            if (!Schema::hasColumn('cash_registers', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
        });

        // Fix cash_register_transactions table - add all potentially missing columns
        if (Schema::hasTable('cash_register_transactions')) {
            Schema::table('cash_register_transactions', function (Blueprint $table) {
                // Add timestamps if missing
                if (!Schema::hasColumn('cash_register_transactions', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('cash_register_transactions', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }

                // Add reference_number if missing
                if (!Schema::hasColumn('cash_register_transactions', 'reference_number')) {
                    $table->string('reference_number')->nullable();
                }

                // Add created_by if missing
                if (!Schema::hasColumn('cash_register_transactions', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }

                // Add soft deletes if missing
                if (!Schema::hasColumn('cash_register_transactions', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to drop these columns as it could cause data loss
        // The down method is intentionally left minimal
    }
};
