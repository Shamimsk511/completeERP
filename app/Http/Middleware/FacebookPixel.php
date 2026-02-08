<?php
// app/Http/Middleware/FacebookPixel.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FacebookPixel
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Add Facebook Pixel tracking code
        if (config('services.facebook.pixel_id')) {
            $pixelCode = view('partials.facebook-pixel', [
                'pixel_id' => config('services.facebook.pixel_id')
            ])->render();
            
            $content = $response->getContent();
            $content = str_replace('</head>', $pixelCode . '</head>', $content);
            $response->setContent($content);
        }
        
        return $response;
    }
}