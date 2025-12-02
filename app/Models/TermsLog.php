<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsLog extends Model
{
    protected $table = 'termslogtbl';

    protected $fillable = ['name', 'status'];
}

