<?php

namespace App\Providers;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AdminLTEServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add AdminLTE assets to auth pages
        if (request()->is('login') || request()->is('register') || request()->is('password/*')) {
            Vite::useScriptTagAttributes([
                'data-auth-page' => true,
            ]);
        }
}
}
