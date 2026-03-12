<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'auth_audit_log';

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];
}
