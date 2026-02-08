<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    protected function setPageData($title, $breadcrumbs = [])
    {
        view()->share([
            'pageTitle' => $title,
            'breadcrumbs' => $breadcrumbs
        ]);
    }
    // In your controller or middleware
public function boot()
{
    view()->composer('*', function ($view) {
        $routeName = request()->route()->getName();
        $view->with('currentRoute', $routeName);
    });
}

}
