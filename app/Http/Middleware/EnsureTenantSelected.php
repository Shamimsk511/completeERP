<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($request->routeIs('tenants.select', 'tenants.switch', 'tenants.assign-existing')) {
            return $next($request);
        }

        $tenantId = TenantContext::currentId();

        if (!$tenantId && $user->tenant_id) {
            TenantContext::set((int) $user->tenant_id);
            $tenantId = (int) $user->tenant_id;
        }

        if (!$tenantId) {
            return redirect()
                ->route('tenants.select')
                ->with('error', 'Please select a company to continue.');
        }

        if (!$user->canAccessTenant($tenantId)) {
            Auth::logout();
            TenantContext::clear();

            return redirect()
                ->route('login')
                ->with('error', 'You are not assigned to the selected company.');
        }

        return $next($request);
    }
}
