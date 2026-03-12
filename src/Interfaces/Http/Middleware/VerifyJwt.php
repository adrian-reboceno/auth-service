<?php

namespace Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Domain\Token\JtiBlacklistRepositoryInterface;
use Illuminate\Support\Facades\Config;

final class VerifyJwt
{
    public function __construct(
        private readonly JtiBlacklistRepositoryInterface $blacklist
    ) {
    }

    /**
     * HIGH-05: Stateless RS256 validation.
     * HIGH-06: JTI Blacklist check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $publicKeyPath = Config::get('jwt.public_key_path');
            if (!file_exists($publicKeyPath)) {
                throw new \RuntimeException('JWT public key not found.');
            }

            $publicKey = file_get_contents($publicKeyPath);

            // SEC-06 & HIGH-05: Use RS256 strictly for decoding
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            $jti = $decoded->jti ?? null;
            if (!$jti) {
                return response()->json(['error' => 'invalid_token_payload'], Response::HTTP_UNAUTHORIZED);
            }

            // HIGH-06: Check JTI Blacklist for critical events ONLY (SEC-05)
            if ($this->blacklist->exists($jti)) {
                return response()->json(['error' => 'token_revoked'], Response::HTTP_UNAUTHORIZED);
            }

            // Inject claims into request for controllers/other middlewares
            $request->attributes->set('jwt_payload', (array) $decoded);
            $request->attributes->set('jti', $jti);
            $request->attributes->set('user_id', $decoded->sub);
            
            // Pass the current roles and permissions to request
            $request->attributes->set('user_roles', $decoded->roles ?? []);
            $request->attributes->set('user_permissions', $decoded->permissions ?? []);

        } catch (\Exception $e) {
            // Expired, invalid signature, or wrong config
            return response()->json(['error' => 'unauthorized', 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
