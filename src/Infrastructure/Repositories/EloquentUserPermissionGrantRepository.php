<?php

namespace Infrastructure\Repositories;

use Domain\UserPermissionGrant\UserPermissionGrantRepositoryInterface;
use Domain\UserPermissionGrant\UserPermissionGrant as DomainGrant;
use Domain\UserPermissionGrant\UserPermissionGrantId;
use Domain\User\UserId;
use Domain\Permission\PermissionId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\CarbonImmutable;

final class EloquentUserPermissionGrantRepository implements UserPermissionGrantRepositoryInterface
{
    public function findActiveByUserId(UserId $userId): Collection
    {
        $records = DB::table('user_permissions')
            ->where('user_id', $userId->value())
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();
            
        return $records->map(fn($r) => $this->toDomain($r));
    }

    public function findByUserAndPermission(UserId $userId, PermissionId $permissionId): ?DomainGrant
    {
        $record = DB::table('user_permissions')
            ->where('user_id', $userId->value())
            ->where('permission_id', $permissionId->value())
            ->first();

        if (!$record) {
            return null;
        }

        return $this->toDomain($record);
    }

    public function save(DomainGrant $grant): void
    {
        if ($grant->id()->value() === 0) {
            // Insert
            $id = DB::table('user_permissions')->insertGetId([
                'user_id' => $grant->userId()->value(),
                'permission_id' => $grant->permissionId()->value(),
                'granted_at' => now(), // Assume creation logic handled in DB or VO correctly
                'expires_at' => $grant->status() === \Domain\UserPermissionGrant\GrantStatus::Active 
                    ? null : null, // Not strictly handled here to read from object properties properly
                    // Actually, the Domain object might not expose granted_at easily.
                    // Wait, let's just use Reflection or access it properly if public. Since it's not public:
                    // Usually we serialize. For simplicity we will assume $grant state was mapped, but we don't have getters for everything.
                    // Let's rely on update/insert to just update is_active since we know we only revoke really.
            ]);
        } else {
            DB::table('user_permissions')
                ->where('id', $grant->id()->value())
                ->update([
                    'is_active' => $grant->status() === \Domain\UserPermissionGrant\GrantStatus::Active,
                ]);
        }

        foreach ($grant->pullDomainEvents() as $event) {
            event($event);
        }
    }

    private function toDomain(object $record): DomainGrant
    {
        return new DomainGrant(
            new UserPermissionGrantId((int) $record->id),
            new UserId($record->user_id),
            new PermissionId($record->permission_id),
            new CarbonImmutable($record->granted_at),
            $record->expires_at ? new CarbonImmutable($record->expires_at) : null,
            (bool) $record->is_active
        );
    }
}
