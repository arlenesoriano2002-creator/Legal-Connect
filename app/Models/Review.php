<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    // Enable mass assignment for these fields
    protected $fillable = ['user_id', 'name', 'email', 'review', 'rating', 'image'];

}
