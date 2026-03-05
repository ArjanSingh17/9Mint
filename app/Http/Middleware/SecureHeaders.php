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

        $csp = "default-src 'self'; " .
            // Allow scripts from your Vite dev server (IPv4, IPv6, and localhost names)
            "script-src 'self' 'unsafe-inline' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173; " .
            // Allow styles from Vite
            "style-src 'self' 'unsafe-inline' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173; " .
            "img-src 'self' data: https:; " .
            "font-src 'self'; " .
            // Allow API connections and WebSockets (for Vite's auto-reload)
            "connect-src 'self' ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173 http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173 http://127.0.0.1:8000;";

        $response->headers->set('Content-Security-Policy', $csp);

        // Hide the PHP version to prevent automated vulnerability scanning
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
