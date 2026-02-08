<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class DebtCollectionSeeder extends Seeder
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

        // Check if debt collection permissions exist, if not create them
        $debtPermissions = [
            'debt-collection-dashboard',
            'debt-collection-track',
            'debt-collection-view-reports',
            'debt-collection-set-priority',
        ];

        foreach ($debtPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Get or create Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        
        // Get or create Manager role  
        $managerRole = Role::firstOrCreate(['name' => 'Admin']);
        
        // Get or create Collection Agent role
        $collectionRole = Role::firstOrCreate(['name' => 'Manager']);

        // Assign all debt collection permissions to Admin
        $adminRole->givePermissionTo($debtPermissions);

        // Assign debt collection permissions to Manager
        $managerRole->givePermissionTo([
            'debt-collection-dashboard',
            'debt-collection-track', 
            'debt-collection-view-reports',
            'debt-collection-set-priority',
        ]);

        // Assign limited permissions to Collection Agent
        $collectionRole->givePermissionTo([
            'debt-collection-dashboard',
            'debt-collection-track',
        ]);

        // Assign Admin role to the first user (assuming it's the main admin)
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole('Admin')) {
            $firstUser->assignRole('Admin');
        }

        // Also assign permissions directly to ensure access
        if ($firstUser) {
            $firstUser->givePermissionTo($debtPermissions);
        }

        $this->command->info('Debt Collection permissions and roles created successfully!');
        $this->command->info('Admin role has all debt collection permissions');
        $this->command->info('Manager role has full debt collection access'); 
        $this->command->info('Collection Agent role has limited access');
        
        if ($firstUser) {
            $this->command->info("User '{$firstUser->name}' has been assigned Admin role with all permissions");
        }
    }
}

// Alternative: Update your existing PermissionTableSeeder.php
// Add this to the end of your existing run() method:

/*
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $collectionRole = Role::firstOrCreate(['name' => 'Collection Agent']);

        // Assign all permissions to Admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to Manager
        $managerPermissions = [
            'debt-collection-dashboard',
            'debt-collection-track',
            'debt-collection-view-reports',
            'debt-collection-set-priority',
            'customer-list',
            'customer-edit',
            'customer-ledger',
            'invoice-list',
            'transaction-list',
        ];
        $managerRole->givePermissionTo($managerPermissions);

        // Assign limited permissions to Collection Agent
        $collectionPermissions = [
            'debt-collection-dashboard',
            'debt-collection-track',
            'customer-list',
            'customer-ledger',
        ];
        $collectionRole->givePermissionTo($collectionPermissions);

        // Assign Admin role to first user
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('Admin');
        }
*/