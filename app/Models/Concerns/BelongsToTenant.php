<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = TenantContext::currentId();
            if (!$tenantId) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });

        static::creating(function ($model) {
            if (!empty($model->tenant_id)) {
                return;
            }

            $tenantId = TenantContext::currentId();
            if ($tenantId) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
