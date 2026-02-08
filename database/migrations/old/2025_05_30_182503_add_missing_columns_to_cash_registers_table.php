<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToCashRegistersTable extends Migration
{
    public function up()
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->text('opening_notes')->nullable()->after('security_pin');
            $table->string('terminal')->default('Terminal 1')->after('opening_notes');
        });
    }

    public function down()
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['opening_notes', 'terminal']);
        });
    }
}
