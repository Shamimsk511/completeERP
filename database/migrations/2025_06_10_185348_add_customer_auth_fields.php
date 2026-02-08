<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerAuthFields extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add last login tracking (optional)
            if (!Schema::hasColumn('customers', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('customers', 'login_count')) {
                $table->integer('login_count')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([ 'last_login_at']);
            // $table->dropIndex(['name', 'id']);
            $table->dropColumn('login_count');
        });
    }
}