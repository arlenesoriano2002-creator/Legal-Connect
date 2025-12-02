<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthColor extends Model
{
    use HasFactory;

    protected $table = 'month_colors';

    protected $fillable = [
        'month',
        'date',
        'color'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    // If you want to automatically manage created_at and updated_at
    public $timestamps = true;
}