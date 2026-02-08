<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'business_settings',
            'customers',
            'categories',
            'companies',
            'products',
            'invoices',
            'invoice_items',
            'transactions',
            'product_returns',
            'product_return_items',
            'purchases',
            'purchase_items',
            'challans',
            'challan_items',
            'other_deliveries',
            'other_delivery_items',
            'other_delivery_returns',
            'other_delivery_return_items',
            'payees',
            'payee_installments',
            'payee_kisti_skips',
            'payable_transactions',
            'cash_registers',
            'cash_register_transactions',
            'debt_collection_trackings',
            'call_logs',
            'call_schedules',
            'financial_years',
            'account_groups',
            'accounts',
            'vouchers',
            'voucher_entries',
            'godowns',
            'product_godown_stocks',
            'employees',
            'employee_attendances',
            'employee_advances',
            'employee_adjustments',
            'employee_payrolls',
            'employee_advance_deductions',
            'tiles_categories',
            'tiles_calculation_settings',
            'colorents',
            'referrers',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('customer_balance_summary') && !Schema::hasColumn('customer_balance_summary', 'tenant_id')) {
            Schema::table('customer_balance_summary', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'business_settings',
            'customers',
            'categories',
            'companies',
            'products',
            'invoices',
            'invoice_items',
            'transactions',
            'product_returns',
            'product_return_items',
            'purchases',
            'purchase_items',
            'challans',
            'challan_items',
            'other_deliveries',
            'other_delivery_items',
            'other_delivery_returns',
            'other_delivery_return_items',
            'payees',
            'payee_installments',
            'payee_kisti_skips',
            'payable_transactions',
            'cash_registers',
            'cash_register_transactions',
            'debt_collection_trackings',
            'call_logs',
            'call_schedules',
            'financial_years',
            'account_groups',
            'accounts',
            'vouchers',
            'voucher_entries',
            'godowns',
            'product_godown_stocks',
            'employees',
            'employee_attendances',
            'employee_advances',
            'employee_adjustments',
            'employee_payrolls',
            'employee_advance_deductions',
            'tiles_categories',
            'tiles_calculation_settings',
            'colorents',
            'referrers',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('customer_balance_summary') && Schema::hasColumn('customer_balance_summary', 'tenant_id')) {
            Schema::table('customer_balance_summary', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
