<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Technician extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'is_active',
        'verification_code',
        'temp_password'
    ];

    protected $hidden = [
        'password',
        'temp_password'
    ];
}
