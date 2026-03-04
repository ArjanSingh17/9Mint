<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if (! empty($user->banned_at)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is banned. Trading, checkout, and wallet actions are disabled.',
                ], 403);
            }

            return redirect()
                ->route('homepage')
                ->with('error', 'Your account is banned. Trading, checkout, and wallet actions are disabled.');
        }

        return $next($request);
    }
}
