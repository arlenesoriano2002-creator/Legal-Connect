<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogBookLogin extends Model
{
    use HasFactory;

    protected $table = 'LogBook_Login';
    protected $fillable = ['username', 'password', 'branch'];

    /**
     * Hash password before saving
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}