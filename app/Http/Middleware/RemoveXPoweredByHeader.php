<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveXPoweredByHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Native PHP function to strip the header before it sends
        header_remove('X-Powered-By');

        // Alternatively, remove it from the Laravel Response object
        if (method_exists($response, 'header')) {
            $response->headers->remove('X-Powered-By');
        }

        return $response;
    }
}