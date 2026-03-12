<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Eloquent model for 'permissions' table.
 */
class Permission extends Model
{
    use HasUuids;

    protected $table = 'permissions';

    protected $fillable = [
        'id',
        'name',
        'description',
    ];
}
