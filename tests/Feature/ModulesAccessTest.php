<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModulesAccessTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'tenant_id' => $tenant->id,
        ]);

        Role::create(['name' => 'Super Admin']);
        $user->assignRole('Super Admin');
        $user->tenants()->attach($tenant->id);

        $this->actingAs($user);
        $this->withSession([TenantContext::SESSION_KEY => $tenant->id]);
    }

    public function test_invoice_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('invoices.index'))
            ->assertOk();
    }

    public function test_challan_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('challans.index'))
            ->assertOk();
    }

    public function test_transaction_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('transactions.index'))
            ->assertOk();
    }

    public function test_purchase_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('purchases.index'))
            ->assertOk();
    }

    public function test_payee_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('payees.index'))
            ->assertOk();
    }

    public function test_payable_transactions_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('payable-transactions.index'))
            ->assertOk();
    }

    public function test_accounting_module_account_groups_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('accounting.account-groups.index'))
            ->assertOk();
    }

    public function test_expense_module_index_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('expenses.index'))
            ->assertOk();
    }
}
