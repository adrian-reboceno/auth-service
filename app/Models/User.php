<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Eloquent model for 'users' table.
 * It is isolated from the Domain Layer (Domain\User\User).
 */
class User extends Model
{
    use HasUuids;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'full_name',
        'email',
        'password_hash',
        'branch_id',
        'locale',
        'is_active',
        'password_changed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password_changed_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
            ->withPivot(['granted_at', 'expires_at', 'is_active']);
    }

    public function activeTokens()
    {
        return $this->hasMany(ActiveToken::class, 'user_id');
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class, 'user_id');
    }
}
