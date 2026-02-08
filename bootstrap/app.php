<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CustomerAuth;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Add alias for common middleware
        $middleware->alias([
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        // Register customer middleware alias
        $middleware->alias([
            'customer.auth' => CustomerAuth::class,
        ]);
         // Configure redirect paths for customer authentication
        $middleware->redirectGuestsTo(function ($request) {
            // If it's a customer route, redirect to customer login
            if ($request->is('customer/*') || $request->routeIs('customer.*')) {
                return route('customer.login');
            }
            // Otherwise, redirect to regular login
            return route('login');
        });
        $middleware->redirectUsersTo(function ($request) {
            // If customer is authenticated, always redirect to customer dashboard
            if (auth()->guard('customer')->check()) {
                return route('customer.dashboard');
            }
            
            // If admin user is authenticated, redirect to admin dashboard
            if (auth()->guard('web')->check()) {
                return route('dashboard');
            }
            
            // Default fallback
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        // Other providers...
        App\Providers\AdminLTEServiceProvider::class,
    ])->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'feature' => \App\Http\Middleware\CheckFeatureEnabled::class,
            'tenant' => \App\Http\Middleware\EnsureTenantSelected::class,
        ]);
    })
    ->create();
  
