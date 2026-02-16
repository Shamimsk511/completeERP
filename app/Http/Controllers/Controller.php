<?php

namespace App\Http\Controllers;

use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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

        $rule = Rule::unique($table, $column)->where(function ($query) use ($tenantId) {
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
                return;
            }

            $query->whereNull('tenant_id');
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
