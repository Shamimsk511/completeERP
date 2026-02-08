<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $backupService = app(\App\Services\TenantBackupService::class);
        $tenants = $this->tenantSummaryQuery()
            ->with([
                'users' => function ($query) {
                    $query->select('users.id', 'users.name', 'users.email');
                },
                'primaryUsers' => function ($query) {
                    $query->select('users.id', 'users.name', 'users.email', 'users.tenant_id');
                },
            ])
            ->orderBy('name')
            ->get();

        $tenantUsers = $tenants->mapWithKeys(function ($tenant) {
            $users = $tenant->users
                ->merge($tenant->primaryUsers)
                ->unique('id')
                ->sortBy('name')
                ->values();

            return [$tenant->id => $users];
        });

        $tenantBackups = $tenants->mapWithKeys(function ($tenant) use ($backupService) {
            return [$tenant->id => $backupService->list($tenant->id)];
        });

        return view('tenants.index', compact('tenants', 'tenantUsers', 'tenantBackups'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tenants,slug'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = $validated['name'];
        $slug = $validated['slug'] ?? $this->uniqueSlug($name);

        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $user = $request->user();
        if ($user) {
            $user->attachTenant($tenant->id, $user->tenant_id === null);
        }

        BusinessSetting::withoutGlobalScope('tenant')->firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'business_name' => $name,
                'phone' => '-',
            ]
        );

        return redirect()->route('tenants.index')->with('success', 'Company created successfully.');
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tenants,slug,' . $tenant->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = $validated['name'];
        $slug = $validated['slug'] ?? $this->uniqueSlug($name);

        $tenant->update([
            'name' => $name,
            'slug' => $slug,
            'is_active' => (bool) ($validated['is_active'] ?? $tenant->is_active),
        ]);

        return redirect()->route('tenants.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenantId = $tenant->id;
        $currentTenantId = TenantContext::currentId();

        DB::transaction(function () use ($tenantId, $tenant, $currentTenantId) {
            BusinessSetting::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->delete();

            DB::table('tenant_user')->where('tenant_id', $tenantId)->delete();

            User::where('tenant_id', $tenantId)->update(['tenant_id' => null]);

            $tenant->delete();

            if ((int) $currentTenantId === (int) $tenantId) {
                TenantContext::clear();
            }
        });

        return redirect()->route('tenants.index')->with('success', 'Company deleted. Related data has been unassigned.');
    }

    public function select(Request $request)
    {
        $tenants = Tenant::orderBy('name')->get();

        if ($tenants->isEmpty()) {
            $default = $this->ensureDefaultTenant();
            $tenants = collect([$default]);
        }

        $currentTenantId = TenantContext::currentId();

        return view('tenants.select', compact('tenants', 'currentTenantId'));
    }

    public function switch(Request $request)
    {
        $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
        ]);

        $tenantId = (int) $request->input('tenant_id');
        $user = $request->user();

        if ($user->hasRole('Super Admin') && !$user->canAccessTenant($tenantId)) {
            $user->attachTenant($tenantId, $user->tenant_id === null);
        }

        if (!$user->canAccessTenant($tenantId)) {
            return redirect()->back()->with('error', 'You are not assigned to this company.');
        }

        TenantContext::set($tenantId);

        return redirect()->back()->with('success', 'Company switched successfully.');
    }

    public function assignExisting(Request $request)
    {
        $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
        ]);

        $tenantId = (int) $request->input('tenant_id');

        $results = $this->assignTenantToExistingData($tenantId);

        return redirect()->back()->with('success', 'Existing data assigned to company.')->with('assign_results', $results);
    }

    protected function ensureDefaultTenant(): Tenant
    {
        $name = 'Rahman Tiles and Sanitary';
        $slug = Str::slug($name);

        return Tenant::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'is_active' => true]
        );
    }

    protected function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function assignTenantToExistingData(int $tenantId): array
    {
        $tables = [
            'users',
            'business_settings',
            'customers',
            'categories',
            'companies',
            'products',
            'invoices',
            'invoice_items',
            'transactions',
            'product_returns',
            'product_return_items',
            'purchases',
            'purchase_items',
            'challans',
            'challan_items',
            'other_deliveries',
            'other_delivery_items',
            'other_delivery_returns',
            'other_delivery_return_items',
            'payees',
            'payee_installments',
            'payee_kisti_skips',
            'payable_transactions',
            'cash_registers',
            'cash_register_transactions',
            'debt_collection_trackings',
            'call_logs',
            'call_schedules',
            'financial_years',
            'account_groups',
            'accounts',
            'godowns',
            'product_godown_stocks',
            'employees',
            'employee_attendances',
            'employee_advances',
            'employee_adjustments',
            'employee_payrolls',
            'employee_advance_deductions',
            'tiles_categories',
            'tiles_calculation_settings',
            'colorents',
            'colorent_purchases',
            'colorent_purchase_items',
            'colorent_usages',
            'referrers',
        ];

        $results = [];

        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table) || !DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                continue;
            }

            $updated = DB::table($table)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);

            $results[$table] = $updated;
        }

        if (DB::getSchemaBuilder()->hasTable('customer_balance_summary')
            && DB::getSchemaBuilder()->hasColumn('customer_balance_summary', 'tenant_id')) {
            $results['customer_balance_summary'] = DB::table('customer_balance_summary')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
        }

        $users = User::all();
        foreach ($users as $user) {
            if ((int) $user->tenant_id === $tenantId) {
                $user->attachTenant($tenantId, false);
            }
        }

        return $results;
    }

    protected function tenantSummaryQuery()
    {
        $query = Tenant::query()->select('tenants.*');

        $query->selectSub(
            DB::table('users')->selectRaw('COUNT(*)')->whereColumn('users.tenant_id', 'tenants.id'),
            'users_count'
        );
        $query->selectSub(
            DB::table('customers')->selectRaw('COUNT(*)')->whereColumn('customers.tenant_id', 'tenants.id'),
            'customers_count'
        );
        $query->selectSub(
            DB::table('products')->selectRaw('COUNT(*)')->whereColumn('products.tenant_id', 'tenants.id'),
            'products_count'
        );
        $query->selectSub(
            DB::table('invoices')->selectRaw('COUNT(*)')->whereColumn('invoices.tenant_id', 'tenants.id'),
            'invoices_count'
        );
        $query->selectSub(
            DB::table('transactions')->selectRaw('COUNT(*)')->whereColumn('transactions.tenant_id', 'tenants.id'),
            'transactions_count'
        );
        $query->selectSub(
            DB::table('accounts')->selectRaw('COUNT(*)')->whereColumn('accounts.tenant_id', 'tenants.id'),
            'accounts_count'
        );

        return $query;
    }
}
