<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Colorent;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissionsToRoles();
        $this->createSuperAdminUser();
        $this->seedColorents();
        
        $this->command->info('Database seeding completed successfully!');
    }

    /**
     * Create all permissions
     */
    private function createPermissions(): void
    {
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
            
            // Other Delivery Return
            'other-delivery-return-list',
            'other-delivery-return-create',
            'other-delivery-return-edit',
            'other-delivery-return-delete',
            'other-delivery-return-print',
            
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
            
            // Colorent Management
            'view colorents',
            'edit colorents',
            'manage stock',
            'update prices'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('Permissions created successfully!');
    }

    /**
     * Create roles
     */
    private function createRoles(): void
    {
        $roles = [
            'Super Admin',
            'Admin', 
            'Manager',
            'Warhouse', // Note: keeping original spelling from your code
            'Collection Agent'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $this->command->info('Roles created successfully!');
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin gets all permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        // Admin gets most permissions
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereNotIn('name', [
                'user-delete',
                'role-delete'
            ])->get();
            $admin->syncPermissions($adminPermissions);
        }

        // Manager permissions
        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $managerPermissions = [
                // Customer management
                'customer-list', 'customer-edit', 'customer-ledger',
                // Invoice management
                'invoice-list', 'invoice-create', 'invoice-edit', 'invoice-print',
                // Product management
                'product-list', 'product-edit',
                // Debt collection
                'debt-collection-dashboard', 'debt-collection-track',
                // Colorent management
                'view colorents', 'edit colorents', 'manage stock',
                // Reports
                'report-sales', 'report-inventory',
                // Transactions
                'transaction-list', 'transaction-create'
            ];
            $manager->givePermissionTo($managerPermissions);
        }

        // Warehouse permissions
        $warehouse = Role::where('name', 'Warhouse')->first();
        if ($warehouse) {
            $warehousePermissions = [
                'product-list',
                'remaining-products-view',
                'view colorents',
                'manage stock',
                'challan-list',
                'challan-create',
                'other-delivery-list',
                'other-delivery-create'
            ];
            $warehouse->givePermissionTo($warehousePermissions);
        }

        // Collection Agent permissions
        $collectionAgent = Role::where('name', 'Collection Agent')->first();
        if ($collectionAgent) {
            $collectionPermissions = [
                'debt-collection-dashboard',
                'debt-collection-track',
                'customer-list',
                'customer-ledger',
                'invoice-list'
            ];
            $collectionAgent->givePermissionTo($collectionPermissions);
        }

        $this->command->info('Permissions assigned to roles successfully!');
    }

    /**
     * Create Super Admin user
     */
    private function createSuperAdminUser(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('sam25524'), // Change this to a secure password
            ]
        );

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole && !$superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info("Super Admin user created with email: {$superAdmin->email}");
    }

    /**
     * Seed colorent data
     */
    private function seedColorents(): void
    {
        $colorents = [
            ['name' => 'COLORANT OC Ochre 1 L', 'stock' => 1, 'price' => 910.00],
            ['name' => 'COLORANT OR Reddish Yellow 1 L', 'stock' => 1, 'price' => 3370.00],
            ['name' => 'COLORANT BC Consent Blue 1 L', 'stock' => 1, 'price' => 2320.00],
            ['name' => 'COLORANT BF Diluted Blue 1 L', 'stock' => 1, 'price' => 1215.00],
            ['name' => 'COLORANT BR Tinting Black 1 L', 'stock' => 1, 'price' => 1225.00],
            ['name' => 'COLORANT GC Concentrte Green 1 L', 'stock' => 1, 'price' => 2595.00],
            ['name' => 'COLORANT GF Diluted Green 1 L', 'stock' => 1, 'price' => 2585.00],
            ['name' => 'COLORANT LM Greenish Yellow 1 L', 'stock' => 1, 'price' => 3020.00],
            ['name' => 'COLORANT MG Magenta 1 L', 'stock' => 1, 'price' => 3700.00],
            ['name' => 'COLORANT NS Medium Yellow 1 L', 'stock' => 1, 'price' => 1510.00],
            ['name' => 'COLORANT NT Concentrte Black 1 L', 'stock' => 1, 'price' => 945.00],
            ['name' => 'COLORANT RD Bright Red (Int) 1 L', 'stock' => 1, 'price' => 2140.00],
            ['name' => 'COLORANT RE Bright Red (Ext) 1 L', 'stock' => 1, 'price' => 4630.00],
            ['name' => 'COLORANT SP Oxide Red 1 L', 'stock' => 1, 'price' => 1845.00],
            ['name' => 'COLORANT VB Red Violet 1 L', 'stock' => 1, 'price' => 1635.00],
            ['name' => 'COLORANT WT White 1 L', 'stock' => 1, 'price' => 1370.00]
        ];

        // Check if colorents already exist to avoid duplicates
        if (Colorent::count() == 0) {
            Colorent::insert($colorents);
            $this->command->info('Colorent data seeded successfully!');
        } else {
            $this->command->info('Colorent data already exists, skipping...');
        }
    }
}
