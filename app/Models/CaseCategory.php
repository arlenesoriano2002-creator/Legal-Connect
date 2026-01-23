<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'case_name',
        'created_at',
        'updated_at'
    ];

    protected $table = 'case_categories';

    // Get all unique categories
    public static function getCategories()
    {
        return self::select('category')
            ->distinct()
            ->orderBy('category')
            ->get()
            ->pluck('category');
    }

    // Get all case names for a specific category
    public static function getCasesByCategory($category)
    {
        return self::where('category', $category)
            ->orderBy('case_name')
            ->get();
    }
}