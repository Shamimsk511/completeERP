<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_payrolls')) {
            Schema::table('employee_payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('employee_payrolls', 'accrual_voucher_id')) {
                    $table->dropForeign(['accrual_voucher_id']);
                    $table->dropColumn('accrual_voucher_id');
                }
                if (Schema::hasColumn('employee_payrolls', 'voucher_id')) {
                    $table->dropForeign(['voucher_id']);
                    $table->dropColumn('voucher_id');
                }
            });
        }

        if (Schema::hasTable('employee_advances')) {
            Schema::table('employee_advances', function (Blueprint $table) {
                if (Schema::hasColumn('employee_advances', 'voucher_id')) {
                    $table->dropForeign(['voucher_id']);
                    $table->dropColumn('voucher_id');
                }
            });
        }

        if (Schema::hasTable('employee_adjustments')) {
            Schema::table('employee_adjustments', function (Blueprint $table) {
                if (Schema::hasColumn('employee_adjustments', 'voucher_id')) {
                    $table->dropForeign(['voucher_id']);
                    $table->dropColumn('voucher_id');
                }
            });
        }

        Schema::dropIfExists('voucher_entries');
        Schema::dropIfExists('vouchers');
    }

    public function down(): void
    {
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_number')->unique();
                $table->enum('voucher_type', [
                    'payment', 'receipt', 'contra', 'journal',
                    'sales', 'purchase', 'debit_note', 'credit_note',
                ]);
                $table->date('voucher_date');

                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();

                $table->string('reference_number')->nullable();
                $table->text('narration')->nullable();
                $table->decimal('total_amount', 15, 2);

                $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
                $table->boolean('is_auto_posted')->default(false);

                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('posted_at')->nullable();

                if (Schema::hasTable('tenants')) {
                    $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete()->index();
                } else {
                    $table->unsignedBigInteger('tenant_id')->nullable()->index();
                }

                $table->timestamps();
                $table->softDeletes();

                $table->index(['voucher_type', 'voucher_date']);
                $table->index(['source_type', 'source_id']);
                $table->index('status');
            });
        }

        if (!Schema::hasTable('voucher_entries')) {
            Schema::create('voucher_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
                $table->foreignId('account_id')->constrained()->onDelete('restrict');

                $table->decimal('debit_amount', 15, 2)->default(0);
                $table->decimal('credit_amount', 15, 2)->default(0);

                $table->text('particulars')->nullable();
                $table->integer('line_order')->default(0);
                $table->unsignedBigInteger('cost_center_id')->nullable();

                if (Schema::hasTable('tenants')) {
                    $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete()->index();
                } else {
                    $table->unsignedBigInteger('tenant_id')->nullable()->index();
                }

                $table->timestamps();

                $table->index(['voucher_id', 'line_order']);
                $table->index('account_id');
            });
        }

        if (Schema::hasTable('employee_payrolls') && !Schema::hasColumn('employee_payrolls', 'voucher_id')) {
            Schema::table('employee_payrolls', function (Blueprint $table) {
                $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            });
        }

        if (Schema::hasTable('employee_payrolls') && !Schema::hasColumn('employee_payrolls', 'accrual_voucher_id')) {
            Schema::table('employee_payrolls', function (Blueprint $table) {
                $table->foreignId('accrual_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            });
        }

        if (Schema::hasTable('employee_advances') && !Schema::hasColumn('employee_advances', 'voucher_id')) {
            Schema::table('employee_advances', function (Blueprint $table) {
                $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            });
        }

        if (Schema::hasTable('employee_adjustments') && !Schema::hasColumn('employee_adjustments', 'voucher_id')) {
            Schema::table('employee_adjustments', function (Blueprint $table) {
                $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            });
        }
    }
};
