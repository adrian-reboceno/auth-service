<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    public $timestamps = false;

    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token_hash',
        'status',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
