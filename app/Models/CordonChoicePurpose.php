<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CordonChoicePurpose extends Model
{
    use HasFactory;

    protected $table = 'cordon_choice_purpose';

    protected $fillable = ['purpose'];
}
