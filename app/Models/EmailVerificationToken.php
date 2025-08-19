<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    protected $fillable = ['email','token','expires_at','used_at'];
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];
}
