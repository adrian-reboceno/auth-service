<?php

namespace Tests\Support\Builders;

use Domain\UserRoleAssignment\UserRoleAssignment;
use Domain\UserRoleAssignment\UserRoleAssignmentId;
use Domain\Role\RoleId;
use Domain\User\UserId;
use Illuminate\Support\Str;

final class UserRoleAssignmentBuilder
{
    public static function forRole(RoleId $roleId): UserRoleAssignment
    {
        return new UserRoleAssignment(
            new UserRoleAssignmentId((int) abs(crc32((string) Str::uuid()))),
            new UserId((string) Str::uuid()),
            $roleId
        );
    }
}
