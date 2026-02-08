<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['tenant_id', 'invoice_date'], 'idx_invoices_tenant_date');
                $table->index(['tenant_id', 'invoice_type'], 'idx_invoices_tenant_type');
                $table->index(['tenant_id', 'customer_id'], 'idx_invoices_tenant_customer');
                $table->index(['tenant_id', 'payment_status'], 'idx_invoices_tenant_payment');
                $table->index(['tenant_id', 'delivery_status'], 'idx_invoices_tenant_delivery');
                $table->index(['tenant_id', 'invoice_number'], 'idx_invoices_tenant_number');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['tenant_id', 'customer_id', 'created_at'], 'idx_transactions_tenant_customer_date');
                $table->index(['tenant_id', 'type', 'created_at'], 'idx_transactions_tenant_type_date');
                $table->index(['tenant_id', 'method', 'created_at'], 'idx_transactions_tenant_method_date');
                $table->index(['tenant_id', 'invoice_id'], 'idx_transactions_tenant_invoice');
                $table->index(['tenant_id', 'return_id'], 'idx_transactions_tenant_return');
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index(['tenant_id', 'name'], 'idx_customers_tenant_name');
                $table->index(['tenant_id', 'phone'], 'idx_customers_tenant_phone');
                $table->index(['tenant_id', 'account_group_id'], 'idx_customers_tenant_group');
                $table->index(['tenant_id', 'outstanding_balance'], 'idx_customers_tenant_balance');
                $table->index(['tenant_id', 'created_at'], 'idx_customers_tenant_created');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['tenant_id', 'name'], 'idx_products_tenant_name');
                $table->index(['tenant_id', 'category_id'], 'idx_products_tenant_category');
                $table->index(['tenant_id', 'company_id'], 'idx_products_tenant_company');
                $table->index(['tenant_id', 'sale_price'], 'idx_products_tenant_sale_price');
                $table->index(['tenant_id', 'current_stock'], 'idx_products_tenant_stock');
                $table->index(['tenant_id', 'created_at'], 'idx_products_tenant_created');
            });
        }

        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->index(['tenant_id', 'invoice_id'], 'idx_invoice_items_tenant_invoice');
                $table->index(['tenant_id', 'product_id'], 'idx_invoice_items_tenant_product');
            });
        }

        if (Schema::hasTable('product_returns')) {
            Schema::table('product_returns', function (Blueprint $table) {
                $table->index(['tenant_id', 'return_date'], 'idx_returns_tenant_date');
                $table->index(['tenant_id', 'customer_id'], 'idx_returns_tenant_customer');
            });
        }

        if (Schema::hasTable('product_return_items')) {
            Schema::table('product_return_items', function (Blueprint $table) {
                $table->index(['tenant_id', 'return_id'], 'idx_return_items_tenant_return');
                $table->index(['tenant_id', 'product_id'], 'idx_return_items_tenant_product');
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->index(['tenant_id', 'purchase_date'], 'idx_purchases_tenant_date');
                $table->index(['tenant_id', 'company_id'], 'idx_purchases_tenant_company');
            });
        }

        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->index(['tenant_id', 'purchase_id'], 'idx_purchase_items_tenant_purchase');
                $table->index(['tenant_id', 'product_id'], 'idx_purchase_items_tenant_product');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('idx_invoices_tenant_date');
                $table->dropIndex('idx_invoices_tenant_type');
                $table->dropIndex('idx_invoices_tenant_customer');
                $table->dropIndex('idx_invoices_tenant_payment');
                $table->dropIndex('idx_invoices_tenant_delivery');
                $table->dropIndex('idx_invoices_tenant_number');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_tenant_customer_date');
                $table->dropIndex('idx_transactions_tenant_type_date');
                $table->dropIndex('idx_transactions_tenant_method_date');
                $table->dropIndex('idx_transactions_tenant_invoice');
                $table->dropIndex('idx_transactions_tenant_return');
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('idx_customers_tenant_name');
                $table->dropIndex('idx_customers_tenant_phone');
                $table->dropIndex('idx_customers_tenant_group');
                $table->dropIndex('idx_customers_tenant_balance');
                $table->dropIndex('idx_customers_tenant_created');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_tenant_name');
                $table->dropIndex('idx_products_tenant_category');
                $table->dropIndex('idx_products_tenant_company');
                $table->dropIndex('idx_products_tenant_sale_price');
                $table->dropIndex('idx_products_tenant_stock');
                $table->dropIndex('idx_products_tenant_created');
            });
        }

        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex('idx_invoice_items_tenant_invoice');
                $table->dropIndex('idx_invoice_items_tenant_product');
            });
        }

        if (Schema::hasTable('product_returns')) {
            Schema::table('product_returns', function (Blueprint $table) {
                $table->dropIndex('idx_returns_tenant_date');
                $table->dropIndex('idx_returns_tenant_customer');
            });
        }

        if (Schema::hasTable('product_return_items')) {
            Schema::table('product_return_items', function (Blueprint $table) {
                $table->dropIndex('idx_return_items_tenant_return');
                $table->dropIndex('idx_return_items_tenant_product');
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex('idx_purchases_tenant_date');
                $table->dropIndex('idx_purchases_tenant_company');
            });
        }

        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropIndex('idx_purchase_items_tenant_purchase');
                $table->dropIndex('idx_purchase_items_tenant_product');
            });
        }
    }
};
