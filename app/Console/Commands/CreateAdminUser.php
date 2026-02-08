<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {name?} {email?} {password?}';
    protected $description = 'Create a super admin user with all permissions';

    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Enter super admin name');
        $email = $this->argument('email') ?? $this->ask('Enter super admin email');
        $password = $this->argument('password') ?? $this->secret('Enter super admin password');

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        // Check if Super Admin role exists, create if not
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if (!$superAdminRole) {
            $superAdminRole = Role::create(['name' => 'Super Admin']);
            
            // Get all permissions and assign to super admin role
            $permissions = Permission::pluck('id')->all();
            $superAdminRole->syncPermissions($permissions);
        }

        // Assign super admin role to user
        $user->assignRole([$superAdminRole->id]);

        $this->info("Super Admin user created successfully!");
        $this->info("Email: $email");
        $this->info("Password: (hidden)");

        return Command::SUCCESS;
    }
}