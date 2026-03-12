<?php

namespace Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckPermission
{
    /**
     * SEC-07: In-memory permission check based on validated JWT payload.
     * No database queries should be done here.
     */
    public function handle(Request $request, Closure $next, string $requiredPermission): Response
    {
        $permissions = $request->attributes->get('user_permissions', []);

        if (!in_array($requiredPermission, $permissions, true)) {
            return response()->json(['error' => 'forbidden', 'message' => 'Missing required permission.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
