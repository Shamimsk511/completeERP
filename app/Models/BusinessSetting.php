<?php
namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use App\Support\TenantContext;

class BusinessSetting extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'business_name', 'email', 'phone', 'address',
        'bin_number', 'logo', 'bank_details',
        'return_policy_days', 'return_policy_message',
        'footer_message', 'timezone', 'theme', 'weekend_days',
        'customer_qr_expiry_days'
    ];

    protected $casts = [
        'weekend_days' => 'array',
    ];
    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget(self::cacheKey($model->tenant_id));
            Cache::forget(self::timezoneCacheKey($model->tenant_id));
        });

        static::deleted(function ($model) {
            Cache::forget(self::cacheKey($model->tenant_id));
            Cache::forget(self::timezoneCacheKey($model->tenant_id));
        });
    }

    public static function cacheKey(?int $tenantId = null): string
    {
        $tenantId = $tenantId ?? TenantContext::currentId();
        return 'business_settings_' . ($tenantId ?: 'global');
    }

    public static function timezoneCacheKey(?int $tenantId = null): string
    {
        $tenantId = $tenantId ?? TenantContext::currentId();
        return 'app_timezone_' . ($tenantId ?: 'global');
    }
}
