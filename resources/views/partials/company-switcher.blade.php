@php
    $currentTenant = \App\Support\TenantContext::current();
    $isSuperAdmin = auth()->check() && auth()->user()->hasRole('Super Admin');
@endphp

@unless($isSuperAdmin)
    <div class="d-flex align-items-center gap-2">
        <span class="badge badge-light">
            Company: {{ $currentTenant?->name ?? 'Not Selected' }}
        </span>
    </div>
@endunless
