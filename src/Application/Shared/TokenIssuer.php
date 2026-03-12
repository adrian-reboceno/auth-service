<?php

namespace Application\Shared;

use Domain\User\User;
use Domain\Token\AccessToken;
use Domain\Token\SessionToken;
use Domain\Token\TokenHash;
use Domain\Services\TokenPayloadBuilder;
use Infrastructure\JWT\TokenGeneratorInterface;
use Domain\Token\TokenRepositoryInterface;
use Domain\Token\ActiveTokenRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Str;

final class TokenIssuer
{
    public function __construct(
        private readonly TokenPayloadBuilder $payloadBuilder,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly TokenRepositoryInterface $refreshTokens,
        private readonly ActiveTokenRepositoryInterface $activeTokens
    ) {
    }

    /**
     * @param string[] $roles
     * @param string[] $permissions
     * @return array{access_token: string, refresh_token: string, expires_in: int, jti: string}
     */
    public function issue(User $user, array $roles, array $permissions): array
    {
        // ADR-01: 5 minutes access token TTL
        $expiresIn = 5 * 60; 
        $accessExpiresAt = (new DateTimeImmutable())->modify("+{$expiresIn} seconds");
        $jti = (string) Str::uuid();

        // 1. Compile payload (Domain layer)
        $payload = $this->payloadBuilder->build($user, $jti, $accessExpiresAt->getTimestamp(), $roles, $permissions);

        // 2. Sign token (Infrastructure layer)
        $accessTokenRaw = $this->tokenGenerator->sign($payload);

        // 3. Register Access token in DB (SEC-03)
        $this->activeTokens->add(new AccessToken($jti, $user->id(), $accessExpiresAt));

        // 4. Create new Refresh Token (8h TTL)
        $rawRefreshToken = (string) Str::uuid() . bin2hex(random_bytes(16));
        $refreshExpiresAt = (new DateTimeImmutable())->modify('+8 hours');
        
        $sessionToken = SessionToken::issue(
            $user->id(), 
            TokenHash::fromRaw($rawRefreshToken), 
            $refreshExpiresAt
        );

        $this->refreshTokens->save($sessionToken);

        return [
            'access_token' => $accessTokenRaw,
            'refresh_token' => $rawRefreshToken,
            'expires_in' => $expiresIn,
            'jti' => $jti
        ];
    }
}
