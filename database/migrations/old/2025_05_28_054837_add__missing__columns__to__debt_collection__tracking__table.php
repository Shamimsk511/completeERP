<?php

// Create this migration file: 
// php artisan make:migration add_missing_columns_to_debt_collection_tracking_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToDebtCollectionTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('debtS_collection_tracking', function (Blueprint $table) {
            // Add follow_up_date column if it doesn't exist
            if (!Schema::hasColumn('debt_collection_tracking', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable()->after('payment_promise_date');
            }
            
            // Add payment_promise_date column if it doesn't exist
            if (!Schema::hasColumn('debt_collection_tracking', 'payment_promise_date')) {
                $table->date('payment_promise_date')->nullable()->after('due_date');
            }
            
            // Add notes column if it doesn't exist
            if (!Schema::hasColumn('debt_collection_tracking', 'notes')) {
                $table->text('notes')->nullable()->after('follow_up_date');
            }
            
            // Add indexes for better performance
            if (!Schema::hasColumn('debt_collection_tracking', 'follow_up_date')) {
                $table->index('follow_up_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('debt_collection_tracking', function (Blueprint $table) {
            $table->dropColumn(['follow_up_date', 'payment_promise_date', 'notes']);
        });
    }
}

// Alternative: If you prefer to check your current table structure first,
// run this command in tinker:
// php artisan tinker
// Schema::getColumnListing('debt_collection_tracking')

// This will show you exactly what columns exist in your table.
// Then you can create a migration with only the missing columns.

// Quick fix without migration - just check what columns exist:
/*
To see your current debt_collection_tracking table structure, run:

In MySQL:
DESCRIBE debt_collection_tracking;

Or in Laravel tinker:
php artisan tinker
Schema::getColumnListing('debt_collection_tracking')

Common columns that might be missing:
- follow_up_date
- payment_promise_date  
- notes

If any of these are missing, add them with:

ALTER TABLE debt_collection_tracking ADD COLUMN follow_up_date DATE NULL;
ALTER TABLE debt_collection_tracking ADD COLUMN payment_promise_date DATE NULL;
ALTER TABLE debt_collection_tracking ADD COLUMN notes TEXT NULL;
*/