<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DynamicRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Different limits based on user role
        $limits = [
            'admin' => 1000,    // Admins get highest limits
            'owner' => 500,     // Owners get high limits
            'renter' => 100,    // Renters get standard limits
            'guest' => 30,      // Unauthenticated users get low limits
        ];

        $userRole = $user?->role ?? 'guest';
        $maxAttempts = $limits[$userRole];

        $key = $this->resolveRequestSignature($request, $user);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error' => 'Too many requests',
                'message' => "Rate limit exceeded for {$userRole} role. Max {$maxAttempts} requests per minute.",
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, 60); // 60 seconds window

        $response = $next($request);

        // Add rate limit headers
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $maxAttempts - RateLimiter::attempts($key),
            'X-RateLimit-Reset' => now()->addSeconds(RateLimiter::availableIn($key))->timestamp,
        ]);

        return $response;
    }

    protected function resolveRequestSignature(Request $request, $user): string
    {
        if ($user) {
            return 'user:'.$user->id.':'.$request->route()->getName();
        }

        return 'ip:'.$request->ip().':'.$request->route()->getName();
    }
}
