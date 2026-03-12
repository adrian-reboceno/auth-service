<?php

namespace Infrastructure\Repositories;

use Domain\Token\ActiveTokenRepositoryInterface;
use Domain\Token\AccessToken;
use Domain\User\UserId;
use App\Models\ActiveToken;

final class EloquentActiveTokenRepository implements ActiveTokenRepositoryInterface
{
    public function add(AccessToken $token): void
    {
        ActiveToken::create([
            "jti"        => $token->jti(),
            "user_id"    => $token->userId()->value(),
            "expires_at" => $token->expiresAt(),
        ]);
    }

    public function removeByJti(string $jti): void
    {
        ActiveToken::where("jti", $jti)->delete();
    }

    public function removeAllForUser(UserId $userId): void
    {
        ActiveToken::where("user_id", $userId->value())->delete();
    }

    public function findAllActiveUserIds(): array
    {
        return ActiveToken::distinct()->pluck("user_id")->toArray();
    }

    /** @return AccessToken[] */
    public function lockAllForUser(UserId $userId): array
    {
        $models = ActiveToken::where("user_id", $userId->value())
            ->lockForUpdate()
            ->get();

        return $models->map(function ($model) {
            return new AccessToken(
                $model->jti,
                new UserId($model->user_id),
                new \DateTimeImmutable($model->expires_at->toDateTimeString())
            );
        })->toArray();
    }
}
