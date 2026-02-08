<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated
        if (!Auth::guard('web')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        // If customer is trying to access admin routes, redirect them
        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
            return redirect()->route('login')
                ->with('info', 'Please log in with admin credentials to access this area.');
        }

        return $next($request);
    }
}
