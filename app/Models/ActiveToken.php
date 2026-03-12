<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveToken extends Model
{
    public $timestamps = false;

    protected $table = 'active_tokens';

    protected $fillable = [
        'jti',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
