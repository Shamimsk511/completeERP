<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public static function getDashboardStats($key, $callback, $ttl = 300)
    {
        return Cache::store('dashboard')->remember(
            "stats:{$key}:" . auth()->id(),
            $ttl,
            $callback
        );
    }
    
    public static function clearDashboardCache($pattern = null)
    {
        if ($pattern) {
            Cache::store('dashboard')->flush();
        } else {
            // Clear specific patterns
            $keys = Cache::store('dashboard')->getRedis()->keys("dashboard:stats:*");
            foreach ($keys as $key) {
                Cache::store('dashboard')->forget(str_replace('dashboard:', '', $key));
            }
        }
    }
    
    public static function getCustomersDropdown()
    {
        return Cache::store('dashboard')->remember(
            'customers:dropdown',
            config('cache.dashboard_ttl.customers', 3600),
            fn() => \App\Models\Customer::select('id', 'name')->orderBy('name')->get()
        );
    }
}