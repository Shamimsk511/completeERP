<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'password')) {
                $table->string('password')->nullable();
            }
            if (!Schema::hasColumn('customers', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }
            if (!Schema::hasColumn('customers', 'password_change_skipped_at')) {
                $table->timestamp('password_change_skipped_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('customers', 'password')) {
                $columns[] = 'password';
            }
            if (Schema::hasColumn('customers', 'password_changed_at')) {
                $columns[] = 'password_changed_at';
            }
            if (Schema::hasColumn('customers', 'password_change_skipped_at')) {
                $columns[] = 'password_change_skipped_at';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
