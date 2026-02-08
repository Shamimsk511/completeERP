<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Challan;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration:
     * 1. Removes the status column from challans (no more pending/delivered/cancelled)
     * 2. Adjusts stock for any pending challans (stock should have been reduced when created)
     * 3. Adds delivered_at timestamp to track when challan was created
     */
    public function up(): void
    {
        // First, adjust stock for any pending challans that haven't reduced stock yet
        $pendingChallans = DB::table('challans')->where('status', 'pending')->get();

        foreach ($pendingChallans as $challan) {
            $items = DB::table('challan_items')->where('challan_id', $challan->id)->get();

            foreach ($items as $item) {
                // Reduce stock for pending items (they should have been reduced when created)
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->decrement('current_stock', $item->quantity);
            }
        }

        // Add delivered_at column to track delivery time
        Schema::table('challans', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('notes');
        });

        // Set delivered_at for existing delivered challans
        DB::table('challans')
            ->where('status', 'delivered')
            ->update(['delivered_at' => DB::raw('updated_at')]);

        // Set delivered_at for pending challans (they are now considered delivered)
        DB::table('challans')
            ->where('status', 'pending')
            ->update(['delivered_at' => DB::raw('created_at')]);

        // Now drop the status column
        Schema::table('challans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('pending')->after('receiver_phone');
        });

        // Restore stock for challans that were previously pending
        // Note: This is a best-effort rollback
        DB::table('challans')->update(['status' => 'delivered']);

        Schema::table('challans', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};
