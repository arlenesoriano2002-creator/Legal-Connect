<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiffunChoicePurpose extends Model
{
    use HasFactory;

    protected $table = 'diffun_choice_purpose';
    
    protected $fillable = ['purpose'];
    
    public $timestamps = true;
}