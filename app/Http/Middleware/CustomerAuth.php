<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if customer is authenticated
        if (!Auth::guard('customer')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            return redirect()->route('customer.login')
                ->with('error', 'Please log in to access your account.');
        }

        // If customer is authenticated, ensure they don't access admin routes
        if ($request->is('dashboard') || 
            $request->is('admin/*') || 
            (!$request->is('customer/*') && !$request->is('/') && !$request->is('homepage'))) {
            
            return redirect()->route('customer.dashboard')
                ->with('info', 'You have been redirected to your customer dashboard.');
        }

        return $next($request);
    }
}