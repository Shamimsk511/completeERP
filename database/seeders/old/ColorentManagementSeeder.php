<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class ColorentManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for colorent management (only if they don't exist)
        $permissions = [
            'view colorents',
            'edit colorents',
            'manage stock',
            'update prices'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Update cache to know about the newly created permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get existing roles and assign permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'view colorents',
                'edit colorents', 
                'manage stock',
                'update prices'
            ]);
        }

        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                'view colorents',
                'edit colorents',
                'manage stock',
                'update prices'
            ]);
        }

        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $manager->givePermissionTo([
                'view colorents',
                'edit colorents',
                'manage stock'
            ]);
        }

        $warehouse = Role::where('name', 'Warhouse')->first();
        if ($warehouse) {
            $warehouse->givePermissionTo([
                'view colorents',
                'manage stock'
            ]);
        }

        echo "Colorent permissions assigned to existing roles successfully!\n";
    }
}
