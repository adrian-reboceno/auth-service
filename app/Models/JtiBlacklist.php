<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JtiBlacklist extends Model
{
    public $timestamps = false;

    protected $table = 'jti_blacklist';

    protected $fillable = [
        'jti',
        'user_id',
        'reason',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
