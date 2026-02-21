<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\PaymentAllocationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Auth;
use App\Auth\CustomerAuthProvider;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Company;
use App\Models\OtherDelivery;
use App\Models\Challan;
use App\Observers\InvoiceObserver;
use App\Observers\PurchaseObserver;
use App\Observers\TransactionObserver;
use App\Observers\CustomerObserver;
use App\Observers\CompanyObserver;
use App\Observers\OtherDeliveryObserver;
use App\Observers\ChallanObserver;
use App\Models\BusinessSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Support\TenantContext;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentAllocationService::class, function ($app) {
            return new PaymentAllocationService();
        });
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
        Gate::define('super-admin-only', function ($user) {
            return $user->hasRole('Super Admin');
        });

        Number::useCurrency('BDT');
        Paginator::useBootstrap();

        // Set application timezone from business settings
        $this->setTimezoneFromSettings();
        $this->setAdminLteBranding();
    if (!function_exists('App\Providers\formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
        // Register custom customer auth provider
        Auth::provider('customer', function ($app, array $config) {
            return new CustomerAuthProvider();
        });

        // Register accounting observers for auto-posting
        Invoice::observe(InvoiceObserver::class);
        Purchase::observe(PurchaseObserver::class);
        Transaction::observe(TransactionObserver::class);
        OtherDelivery::observe(OtherDeliveryObserver::class);
        Challan::observe(ChallanObserver::class);

        // Register observers to auto-create ledger accounts for customers and vendors
        Customer::observe(CustomerObserver::class);
        Company::observe(CompanyObserver::class);
    }

    /**
     * Set application timezone from business settings
     */
    private function setTimezoneFromSettings(): void
    {
        try {
            // Only run for web requests (not console commands during setup)
            if (!$this->app->runningInConsole()) {
                if (Schema::hasTable('business_settings')) {
                    $tenantId = TenantContext::currentId();
                    $cacheKey = BusinessSetting::timezoneCacheKey($tenantId);
                    $timezone = Cache::remember($cacheKey, 3600, function () {
                        $settings = BusinessSetting::first();
                        return $settings?->timezone ?? 'Asia/Dhaka';
                    });

                    if ($timezone && in_array($timezone, timezone_identifiers_list())) {
                        config(['app.timezone' => $timezone]);
                        date_default_timezone_set($timezone);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
            // This allows artisan commands to run during initial setup
        }
    }

    /**
     * Set AdminLTE branding from the selected company.
     */
    private function setAdminLteBranding(): void
    {
        try {
            if ($this->app->runningInConsole()) {
                return;
            }

            if (!Schema::hasTable('tenants')) {
                return;
            }

            $tenant = TenantContext::current();
            if (!$tenant) {
                return;
            }

            $companyName = null;
            if (Schema::hasTable('business_settings')) {
                $settings = BusinessSetting::first();
                $companyName = $settings?->business_name;
            }

            $companyName = $companyName ?: $tenant->name;
            if (!$companyName) {
                return;
            }

            $shortName = Str::limit($companyName, 28, '');

            config([
                'adminlte.title' => $companyName,
                'adminlte.logo' => '<b>' . e($shortName) . '</b>',
                'adminlte.logo_img_alt' => $companyName,
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking requests during setup.
        }
    }
}
