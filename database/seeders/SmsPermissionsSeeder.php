<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SmsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SMS permissions
        $permissions = [
            'sms-view' => 'View SMS Dashboard and Logs',
            'sms-manage' => 'Manage SMS Settings and Providers',
            'sms-send' => 'Send SMS Messages',
            'sms-test' => 'Send Test SMS',
            'sms-bulk' => 'Send Bulk SMS',
            'sms-logs' => 'View SMS Logs',
            'sms-export' => 'Export SMS Data',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ], [
                'description' => $description
            ]);
        }

        // Assign permissions to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(array_keys($permissions));
        }

        // Assign basic permissions to Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'sms-view',
                'sms-send',
                'sms-test',
                'sms-logs'
            ]);
        }

        // Assign view permissions to Manager role
        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'sms-view',
                'sms-logs'
            ]);
        }
    }
}