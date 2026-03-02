<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $table = 'pending_users';

    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'password',
        'email_verification_token',
        'email_verification_expires_at',
    ];
    
    protected $casts = [
        'email_verification_expires_at' => 'datetime',
    ];
}
