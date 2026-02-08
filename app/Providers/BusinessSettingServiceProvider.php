<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Support\TenantContext;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BusinessSettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('business.settings', function () {
            $cacheKey = BusinessSetting::cacheKey(TenantContext::currentId());

            return Cache::remember($cacheKey, 3600, function () { // Cache for 1 hour
                try {
                    return BusinessSetting::first() ?? new BusinessSetting();
                } catch (\Exception $e) {
                    // Handle case when table doesn't exist yet (during migrations)
                    return new BusinessSetting();
                }
            });
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $settings = app('business.settings');
            $tenant = TenantContext::current();
            $companyName = $settings?->business_name ?: $tenant?->name;

            if ($companyName) {
                $shortName = Str::limit($companyName, 28, '');
                config([
                    'adminlte.title' => $companyName,
                    'adminlte.logo' => '<b>' . e($shortName) . '</b>',
                    'adminlte.logo_img_alt' => $companyName,
                ]);
            }

            $view->with('businessSettings', $settings);
        });
    }
}
