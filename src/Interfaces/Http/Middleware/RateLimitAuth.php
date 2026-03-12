<?php

namespace Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

final class RateLimitAuth
{
    /**
     * SEC-04: Strict rate limiting logic.
     * 5 attempts per IP per minute for Auth endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'auth_attempts:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => 'too_many_requests',
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);

        // If successful authentication, we could optionally clear the rate limit
        if ($response->getStatusCode() === Response::HTTP_OK) {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
