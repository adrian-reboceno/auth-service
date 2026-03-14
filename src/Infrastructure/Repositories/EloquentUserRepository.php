<?php

namespace Infrastructure\Repositories;

use Domain\User\UserRepositoryInterface;
use Domain\User\User as DomainUser;
use Domain\User\UserId;
use Domain\User\FullName;
use Domain\User\Email;
use Domain\User\Password;
use Domain\User\Locale;
use App\Models\User as EloquentUser;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?DomainUser
    {
        $model = EloquentUser::find($id->value());
        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }
    
    public function findAll(): \Illuminate\Support\Collection
    {
        return \App\Models\User::all()->map(fn($model) => $this->toDomain($model));
    }

    public function findByEmail(string $email): ?DomainUser
    {
        $model = EloquentUser::where('email', $email)->first();
        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }
    
    public function save(DomainUser $user): void
    {
        $model = EloquentUser::find($user->id()->value());
        if (!$model) {
            $model = new EloquentUser();
            $model->id = $user->id()->value();
        }

        $model->full_name = $user->fullName()->value();
        $model->email = $user->email()->value();
        $model->password_hash = $user->password()->hash();
        $model->branch_id = $user->branchId();
        $model->locale = $user->locale()->value();
        $model->is_active = $user->isActive();
        $model->password_changed_at = $user->passwordChangedAt();
        
        $model->save();
        
        // Dispatch Domain Events
        foreach ($user->pullDomainEvents() as $event) {
            event($event);
        }
    }

    public function countActiveAdmins(): int
    {
        // SEC-02 implementation
        return EloquentUser::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'super_admin')->where('is_active', true);
            })->count();
    }

    private function toDomain(EloquentUser $model): DomainUser
    {
        return new DomainUser(
            new UserId($model->id),
            FullName::fromString($model->full_name),
            Email::fromString($model->email),
            Password::fromHash($model->password_hash),
            $model->branch_id,
            Locale::from($model->locale),
            $model->is_active,
            $model->password_changed_at ? new \DateTimeImmutable($model->password_changed_at->toDateTimeString()) : null
        );
    }
}
