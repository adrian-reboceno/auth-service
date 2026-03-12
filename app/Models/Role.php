<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Eloquent model for 'roles' table.
 */
class Role extends Model
{
    use HasUuids;

    protected $table = 'roles';

    protected $fillable = [
        'id',
        'name',
        'description',
        'is_active',
        'is_system'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
