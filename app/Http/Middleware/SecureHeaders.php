<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
{
    $response = $next($request);
    
    // Prevent site from being displayed in an iframe (Clickjacking)
    $response->headers->set('X-Frame-Options', 'DENY');
    
    // Prevent browser from guessing content types (MIME Sniffing)
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    
    return $response;
}
}
