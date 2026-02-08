<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAndReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_show_displays_weight_and_derived_values(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'is_active' => true,
        ]);

        TenantContext::set($tenant->id);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'box_pcs' => 5,
            'pieces_feet' => 1.25,
            'weight_value' => 10.0,
            'weight_unit' => 'per_box',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['tenant_id' => $tenant->id])
            ->get(route('categories.show', $category));

        $response->assertStatus(200);
        $response->assertSee('Weight');
        $response->assertSee('Per Piece:');
        $response->assertSee('KG');
    }

    public function test_returns_create_page_renders_for_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'is_active' => true,
        ]);

        TenantContext::set($tenant->id);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'address' => 'Test Address',
            'opening_balance' => 0,
            'outstanding_balance' => 0,
            'tenant_id' => $tenant->id,
        ]);

        $company = Company::create([
            'name' => 'Test Company',
            'tenant_id' => $tenant->id,
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'box_pcs' => 5,
            'pieces_feet' => 1.25,
            'tenant_id' => $tenant->id,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test',
            'opening_stock' => 10,
            'current_stock' => 10,
            'purchase_price' => 50,
            'sale_price' => 100,
            'is_stock_managed' => true,
            'company_id' => $company->id,
            'category_id' => $category->id,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['tenant_id' => $tenant->id])
            ->withoutMiddleware()
            ->get(route('returns.create'));

        $response->assertStatus(200);
        $response->assertSee('Return Details');
        $response->assertSee('Test Customer');
        $response->assertSee('Test Product');
    }
}
