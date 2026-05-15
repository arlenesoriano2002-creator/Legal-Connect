<?php

namespace App\Models;

class StaffUser extends User
{
    protected $table = 'users';

    protected $fillable = [
    'name',
    'email',
    'address',
    'cp_number',
    'role',
    'law_office',    // <--- CRITICAL: Make sure this is here
    'law_office_id',
    'password',
    'image',
    'active_status',
    'is_verified',
    ];

    public function lawOffice()
{
    return $this->belongsTo(LawOffice::class, 'law_office_id');
}
}
