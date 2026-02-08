<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Session;

class TenantContext
{
    public const SESSION_KEY = 'tenant_id';

    public static function currentId(): ?int
    {
        $tenantId = Session::get(self::SESSION_KEY);

        return $tenantId ? (int) $tenantId : null;
    }

    public static function current(): ?Tenant
    {
        $tenantId = self::currentId();
        if (!$tenantId) {
            return null;
        }

        return Tenant::find($tenantId);
    }

    public static function set(int $tenantId): void
    {
        Session::put(self::SESSION_KEY, $tenantId);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
