<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
    'user_id',
    'session_id',
    'ip_address',
    'user_agent',
    'browser',
    'platform',
    'login_at',
    'logout_at',
    'is_active',
];
}
