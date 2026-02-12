<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    // Check if the user is logged in
    if (!auth()->check()) {
        return redirect('/login');
    }

    // Check if the user's role is NOT 'admin'
    if (auth()->user()->role !== 'admin') {
        abort(403, 'Unauthorized action.'); // Show a "Forbidden" error
    }

    // If they are an admin, let them proceed
    return $next($request);
}
}
