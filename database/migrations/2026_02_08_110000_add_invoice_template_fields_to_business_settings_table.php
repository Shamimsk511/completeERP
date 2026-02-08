<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('business_settings', 'invoice_template')) {
                $table->string('invoice_template')->default('standard')->after('weekend_days');
            }

            if (!Schema::hasColumn('business_settings', 'invoice_print_options')) {
                $table->json('invoice_print_options')->nullable()->after('invoice_template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'invoice_print_options')) {
                $table->dropColumn('invoice_print_options');
            }

            if (Schema::hasColumn('business_settings', 'invoice_template')) {
                $table->dropColumn('invoice_template');
            }
        });
    }
};
