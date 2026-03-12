<?php

namespace Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckRole
{
    /**
     * Checks if the user holds a specific role (read from JWT).
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $roles = $request->attributes->get('user_roles', []);

        if (!in_array($requiredRole, $roles, true)) {
            return response()->json(['error' => 'forbidden', 'message' => 'Missing required role.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
