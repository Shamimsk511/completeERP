<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Business Settings
            'business-settings-view',
            'business-settings-edit',
            
            // Cash Register
            'cash-register-list',
            'cash-register-open',
            'cash-register-close',
            'cash-register-add-transaction',
            'cash-register-report',
            'cash-register-delete',
            'cash-register-void-transaction',
            'cash-register-access-all',
            
            // Challan
            'challan-list',
            'challan-create',
            'challan-edit',
            'challan-delete',
            'challan-print',
            
            // Customer
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'customer-ledger',
            'customer-import',
            'customer-export',
            
            // Debt Collection
            'debt-collection-dashboard',
            'debt-collection-track',
            'debt-collection-view-reports',
            'debt-collection-set-priority',
            
            // Invoice
            'invoice-list',
            'invoice-create',
            'invoice-edit',
            'invoice-delete',
            'invoice-print',
            'invoice-export',
            'invoice-mark-paid',
            'invoice-update-delivery-status',
            
            // Other Delivery
            'other-delivery-list',
            'other-delivery-create',
            'other-delivery-edit',
            'other-delivery-delete',
            'other-delivery-print',
            
            // Payable Transaction
            'payable-transaction-list',
            'payable-transaction-create',
            'payable-transaction-edit',
            'payable-transaction-delete',
            
            // Payee
            'payee-list',
            'payee-create',
            'payee-edit',
            'payee-delete',
            'payee-ledger',
            'payee-reports',
            
            // Products
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'product-import',
            'product-export',
            
            // Categories
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
            
            // Suppliers
            'supplier-list',
            'supplier-create',
            'supplier-edit',
            'supplier-delete',
            'supplier-ledger',
            
            // Purchases
            'purchase-list',
            'purchase-create',
            'purchase-edit',
            'purchase-delete',
            'purchase-print',

            // Godowns
            'godown-list',
            'godown-create',
            'godown-edit',
            'godown-delete',
            
            // Returns
            'return-list',
            'return-create',
            'return-edit',
            'return-delete',
            'return-print',
            
            // Expenses
            'expense-list',
            'expense-create',
            'expense-edit',
            'expense-delete',
            'expense-report',
            
            // Transactions
            'transaction-list',
            'transaction-create',
            'transaction-edit',
            'transaction-delete',
            
            // Remaining Products
            'remaining-products-view',
            
            // Profile
            'profile-edit',
            'profile-delete',
            
            // Reports
            'report-sales',
            'report-inventory',
            'report-financial',
            'report-export',
            
            // User Management
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
}
}
