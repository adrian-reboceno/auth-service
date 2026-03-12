<?php

namespace Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * SEC-13: Strict locale whitelist validation
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = config('app.supported_locales', ['es', 'en']);

        // 1. From JWT
        if ($jwt = $request->get('jwt_payload')) { // Assuming VerifyJwt middleware sets this
            $locale = $jwt->locale ?? null;
            if (is_string($locale) && in_array($locale, $supported, true)) {
                return $locale;
            }
        }

        // 2. From Accept-Language header
        $header = $request->getPreferredLanguage($supported);
        if ($header && in_array($header, $supported, true)) {
            return $header;
        }

        // 3. Fallback
        return config('app.locale', 'es');
    }
}
