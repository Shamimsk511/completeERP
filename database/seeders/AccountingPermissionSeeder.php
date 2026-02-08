<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccountingPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Accounting permissions
        $permissions = [
            // Account Management
            'account-list',
            'account-create',
            'account-edit',
            'account-delete',

            // Accounting Reports
            'accounting-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all accounting permissions to Super Admin role (if exists)
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Create Accountant role with accounting permissions
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->givePermissionTo($permissions);

        $this->command->info('Accounting permissions created successfully!');
    }
}
