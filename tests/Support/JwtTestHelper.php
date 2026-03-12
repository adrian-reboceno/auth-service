<?php

namespace Tests\Support;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

trait JwtTestHelper
{
    /** Signs a JWT with the RSA test key defined in .env.testing */
    protected function forgeJwtWithClaims(array $overrides = []): string
    {
        $keyPath = env('JWT_PRIVATE_KEY_PATH');
        if (!file_exists($keyPath)) {
            throw new \RuntimeException("Test private key not found at {$keyPath}. Run php artisan auth:generate-test-keys");
        }
        
        $key = file_get_contents($keyPath);
        $payload = array_merge([
            'iss' => 'pharmacy-auth-service',
            'sub' => (string) Str::uuid(),
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(5)->timestamp,
            'jti' => (string) Str::uuid(),
            'roles'       => [],
            'permissions' => [],
            'locale'      => 'es',
            'branch_id'   => 1,
        ], $overrides);
        
        return JWT::encode($payload, $key, 'RS256');
    }

    /** Login and return [accessToken, refreshToken] */
    protected function loginAndGetTokens(User $user): array
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'Password1', // standard test password for all User factories
        ]);
        
        return [
            $response->json('data.access_token'),
            $response->json('data.refresh_token'),
        ];
    }
}
