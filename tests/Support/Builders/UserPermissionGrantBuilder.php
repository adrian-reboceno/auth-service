<?php

namespace Tests\Support\Builders;

use Domain\UserPermissionGrant\UserPermissionGrant;
use Domain\UserPermissionGrant\UserPermissionGrantId;
use Domain\User\UserId;
use Domain\Permission\PermissionId;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;

final class UserPermissionGrantBuilder
{
    private string $permissionName;
    private bool $isActive = true;
    private ?CarbonImmutable $expiresAt = null;

    private function __construct(string $permissionName)
    {
        $this->permissionName = $permissionName;
    }

    public static function active(string $permissionName): self
    {
        return new self($permissionName);
    }

    public static function expired(string $permissionName): self
    {
        $builder = new self($permissionName);
        $builder->expiresAt = CarbonImmutable::now()->subDay();
        return $builder;
    }

    public static function revoked(string $permissionName): self
    {
        $builder = new self($permissionName);
        $builder->isActive = false;
        return $builder;
    }

    public static function noExpiry(): self
    {
        return new self('test:permission');
    }

    public static function expiresAt(\Carbon\Carbon $date): self
    {
        $builder = new self('test:permission');
        $builder->expiresAt = CarbonImmutable::instance($date);
        return $builder;
    }

    public function build(): UserPermissionGrant
    {
        // Mock PermissionId via MD5 of name
        $uuid = substr(md5($this->permissionName), 0, 8) . '-0000-0000-0000-000000000000';
        $permissionId = new PermissionId($uuid);

        return new UserPermissionGrant(
            new UserPermissionGrantId((int) abs(crc32((string) Str::uuid()))),
            new UserId((string) Str::uuid()),
            $permissionId,
            CarbonImmutable::now(), // granted_at
            $this->expiresAt,
            $this->isActive
        );
    }
}
