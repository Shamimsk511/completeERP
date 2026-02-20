<?php

namespace App\Http\Controllers;

use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Facades\Schema;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function tenantUniqueRule(
        string $table,
        string $column = 'NULL',
        ?int $ignoreId = null,
        string $idColumn = 'id'
    ): Unique {
        $tenantId = TenantContext::currentId();

        $rule = Rule::unique($table, $column)->where(function ($query) use ($tenantId, $table) {
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            } else {
                $query->whereNull('tenant_id');
            }

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }
        });

        if ($ignoreId !== null) {
            $rule->ignore($ignoreId, $idColumn);
        }

        return $rule;
    }
    protected function setPageData($title, $breadcrumbs = [])
    {
        view()->share([
            'pageTitle' => $title,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function boot()
    {
        view()->composer('*', function ($view) {
            $routeName = request()->route()->getName();
            $view->with('currentRoute', $routeName);
        });
    }
}
