<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ErpFeatureSetting;
use Illuminate\Support\Facades\Schema;

class UpdatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder is designed to be run on existing projects to add new permissions
     * without affecting existing data. It uses firstOrCreate to avoid duplicates.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Starting permissions update...');

        // All new permissions to add
        $newPermissions = [
            // ERP Feature Settings (added 2026-01-30)
            'erp-features-view',
            'erp-features-edit',

            // Other Delivery Returns (if not already present)
            'other-delivery-return-list',
            'other-delivery-return-create',
            'other-delivery-return-edit',
            'other-delivery-return-delete',
            'other-delivery-return-print',

            // Accounting Module permissions
            'account-list',
            'account-create',
            'account-edit',
            'account-delete',
            'account-group-list',
            'account-group-create',
            'account-group-edit',
            'account-group-delete',
            'accounting-reports',

            // Payroll/HR permissions
            'employee-list',
            'employee-create',
            'employee-edit',
            'employee-delete',
            'employee-attendance',
            'employee-payroll',
            'employee-advance',
            'employee-adjustment',

            // SMS permissions
            'sms-settings-view',
            'sms-settings-edit',
            'sms-send',
            'sms-logs-view',

            // Godown permissions
            'godown-list',
            'godown-create',
            'godown-edit',
            'godown-delete',

            // Expense permissions
            'expense-list',
            'expense-create',
            'expense-edit',
            'expense-delete',
            'expense-report',

            // Customer permissions (safety refresh for tightened middleware)
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'customer-ledger',
            'customer-import',
            'customer-export',

            // Chat permissions
            'chat-access',
            'chat-message-send',
            'chat-clear',
        ];

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($newPermissions as $permission) {
            $exists = Permission::where('name', $permission)->exists();

            if (!$exists) {
                Permission::create(['name' => $permission]);
                $addedCount++;
                $this->command->info("  + Added permission: {$permission}");
            } else {
                $skippedCount++;
            }
        }

        $this->command->info("Permissions: {$addedCount} added, {$skippedCount} already existed.");

        // Assign new permissions to Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::all());
            $this->command->info('Super Admin role updated with all permissions.');
        }

        // Ensure Admin and Manager roles have expense permissions
        $expensePermissions = [
            'expense-list',
            'expense-create',
            'expense-edit',
            'expense-delete',
            'expense-report',
        ];

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($expensePermissions);
            $this->command->info('Admin role updated with expense permissions.');
        }

        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo($expensePermissions);
            $this->command->info('Manager role updated with expense permissions.');
        }

        // Ensure Admin and Manager roles have customer permissions
        $adminCustomerPermissions = [
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'customer-ledger',
            'customer-import',
            'customer-export',
        ];

        $managerCustomerPermissions = [
            'customer-list',
            'customer-edit',
            'customer-ledger',
        ];

        $chatPermissions = [
            'chat-access',
            'chat-message-send',
        ];

        $adminChatPermissions = [
            'chat-clear',
        ];

        // Team chat should be available to core operational roles.
        $chatRoleNames = [
            'Admin',
            'Manager',
            'Warhouse',
            'Warehouse',
            'Accountant',
            'Demo User',
        ];

        if ($adminRole) {
            $adminRole->givePermissionTo($adminCustomerPermissions);
            $this->command->info('Admin role updated with customer permissions.');
        }

        if ($managerRole) {
            $managerRole->givePermissionTo($managerCustomerPermissions);
            $this->command->info('Manager role updated with customer permissions.');
        }

        $chatRoles = Role::whereIn('name', $chatRoleNames)->get();
        foreach ($chatRoles as $chatRole) {
            $chatRole->givePermissionTo($chatPermissions);
            $this->command->info("{$chatRole->name} role updated with chat permissions.");
        }

        if ($adminRole) {
            $adminRole->givePermissionTo($adminChatPermissions);
            $this->command->info('Admin role updated with chat admin permissions.');
        }

        foreach ($chatRoles as $chatRole) {
            if ($chatRole->name === 'Admin') {
                continue;
            }

            try {
                $chatRole->revokePermissionTo($adminChatPermissions);
            } catch (\Exception $e) {
                // Ignore if permissions are not assigned yet.
            }
        }

        // Seed ERP Feature Settings if table exists
        if (Schema::hasTable('erp_feature_settings')) {
            ErpFeatureSetting::seedDefaults();
            $this->command->info('ERP Feature settings seeded.');
        } else {
            $this->command->warn('ERP Feature settings table does not exist. Run migrations first.');
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Permissions update completed successfully!');
    }
}
