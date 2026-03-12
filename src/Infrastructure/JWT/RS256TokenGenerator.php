<?php

namespace Infrastructure\JWT;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;

final class RS256TokenGenerator implements TokenGeneratorInterface
{
    public function sign(array $payload): string
    {
        $privateKeyPath = config('jwt.private_key_path');
        
        if (!file_exists($privateKeyPath)) {
            throw new \RuntimeException("JWT private key not found at {$privateKeyPath}");
        }

        $privateKey = file_get_contents($privateKeyPath);

        // SEC-06: Explicitly set RS256 algorithm
        return JWT::encode($payload, $privateKey, 'RS256');
    }
}
