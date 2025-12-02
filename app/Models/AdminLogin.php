<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminLogin extends Authenticatable
{
    protected $table = 'admin_login_form';

    protected $fillable = [
    'username',
    'password',
    'image',
    'role',
];


    protected $hidden = [
        'password',
    ];

    // Disable timestamps since your table doesn’t have created_at/updated_at
    public $timestamps = false;
}
