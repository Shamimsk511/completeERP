<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * These fields store snapshot data at invoice creation time:
     * - previous_balance: Customer's outstanding balance BEFORE this invoice
     * - initial_paid_amount: Payment made at time of invoice creation (not affected by reallocation)
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Customer's outstanding balance at the time of invoice creation (before this invoice)
            $table->decimal('previous_balance', 12, 2)->default(0)->after('due_amount');

            // The payment amount made at the time of invoice creation
            // This is separate from paid_amount which gets reallocated by PaymentAllocationService
            $table->decimal('initial_paid_amount', 12, 2)->default(0)->after('previous_balance');
        });

        // Backfill existing invoices with current paid_amount as initial_paid_amount
        // This isn't historically accurate but prevents issues with existing data
        DB::statement('UPDATE invoices SET initial_paid_amount = paid_amount WHERE initial_paid_amount = 0 AND paid_amount > 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['previous_balance', 'initial_paid_amount']);
        });
    }
};
