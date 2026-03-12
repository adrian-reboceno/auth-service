<?php

namespace Infrastructure\Repositories;

use Domain\UserRoleAssignment\UserRoleAssignmentRepositoryInterface;
use Domain\UserRoleAssignment\UserRoleAssignment as DomainAssignment;
use Domain\UserRoleAssignment\UserRoleAssignmentId;
use Domain\User\UserId;
use Domain\Role\RoleId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class EloquentUserRoleAssignmentRepository implements UserRoleAssignmentRepositoryInterface
{
    public function findByUserId(UserId $userId): Collection
    {
        $records = DB::table('user_roles')->where('user_id', $userId->value())->get();
        return $records->map(fn($r) => $this->toDomain($r));
    }

    public function findByUserAndRole(UserId $userId, RoleId $roleId): ?DomainAssignment
    {
        $record = DB::table('user_roles')
            ->where('user_id', $userId->value())
            ->where('role_id', $roleId->value())
            ->first();

        if (!$record) {
            return null;
        }

        return $this->toDomain($record);
    }

    public function save(DomainAssignment $assignment): void
    {
        if ($assignment->id()->value() === 0) {
            // Insert
            $id = DB::table('user_roles')->insertGetId([
                'user_id' => $assignment->userId()->value(),
                'role_id' => $assignment->roleId()->value(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Conceptually we'd set the ID back on the assignment, but DDD Aggregate handles its own state.
        } else {
            // No easy way to update an assignment beyond just re-inserting if it doesn't exist, pivot tables don't usually change just ID.
        }

        foreach ($assignment->pullDomainEvents() as $event) {
            event($event);
        }
    }

    public function delete(DomainAssignment $assignment): void
    {
        DB::table('user_roles')
            ->where('user_id', $assignment->userId()->value())
            ->where('role_id', $assignment->roleId()->value())
            ->delete();

        foreach ($assignment->pullDomainEvents() as $event) {
            event($event);
        }
    }
    
    public function hasAssignments(RoleId $roleId): bool
    {
        return DB::table('user_roles')->where('role_id', $roleId->value())->exists();
    }

    private function toDomain(object $record): DomainAssignment
    {
        return new DomainAssignment(
            new UserRoleAssignmentId((int) $record->id),
            new UserId($record->user_id),
            new RoleId($record->role_id)
        );
    }
}
