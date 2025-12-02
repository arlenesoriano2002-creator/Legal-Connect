<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekColor extends Model
{
    use HasFactory;

    protected $table = 'week_colors';

    protected $fillable = [
        'date',
        'time',
        'color'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    // If you want to automatically manage created_at and updated_at
    public $timestamps = true;
}