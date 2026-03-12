<?php

namespace Infrastructure\Repositories;

use Domain\Token\TokenRepositoryInterface;
use Domain\Token\SessionToken;
use Domain\Token\SessionTokenId;
use Domain\Token\TokenHash;
use Domain\Token\TokenStatus;
use Domain\User\UserId;
use App\Models\RefreshToken;

final class EloquentTokenRepository implements TokenRepositoryInterface
{
    public function findByHash(TokenHash $hash): ?SessionToken
    {
        $model = RefreshToken::where('token_hash', $hash->value())->first();
        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(SessionToken $token): void
    {
        $attributes = [
            'user_id' => $token->userId()->value(),
            'token_hash' => $token->tokenHash()->value(),
            'status' => $token->status()->value,
            'expires_at' => $token->expiresAt(),
        ];

        if ($token->id() === null) {
            $model = RefreshToken::create($attributes);
            $token->setId(new SessionTokenId($model->id));
        } else {
            $model = RefreshToken::find($token->id()->value());
            $model->update($attributes);
        }

        foreach ($token->pullDomainEvents() as $event) {
            event($event);
        }
    }
    
    public function revokeAllForUser(UserId $userId): void
    {
        RefreshToken::where('user_id', $userId->value())
            ->whereIn('status', [TokenStatus::Active->value, TokenStatus::Used->value])
            ->update(['status' => TokenStatus::Revoked->value]);
    }

    private function toDomain(RefreshToken $model): SessionToken
    {
        return new SessionToken(
            new SessionTokenId($model->id),
            new UserId($model->user_id),
            TokenHash::fromHash($model->token_hash),
            TokenStatus::from($model->status),
            new \DateTimeImmutable($model->expires_at->toDateTimeString())
        );
    }
}
