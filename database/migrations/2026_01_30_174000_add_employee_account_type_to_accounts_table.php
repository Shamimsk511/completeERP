<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `accounts` MODIFY `account_type` ENUM('cash','bank','customer','supplier','employee','expense','income','asset','liability','capital','suspense')");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `accounts` MODIFY `account_type` ENUM('cash','bank','customer','supplier','expense','income','asset','liability','capital','suspense')");
    }
};
