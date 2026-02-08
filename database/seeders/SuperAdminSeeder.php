<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Create a Super Admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        
        // Get all permissions
        $permissions = Permission::all();
        
        // Assign all permissions to Super Admin role
        $superAdminRole->syncPermissions($permissions);
        
        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gamil.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('sam25524'), // Change this to a secure password
            ]
        );
        
        // Assign Super Admin role to user
        $superAdmin->assignRole($superAdminRole);
    }
}
