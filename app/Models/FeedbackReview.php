<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackReview extends Model
{
    use HasFactory;

    protected $table = 'reviews'; // Explicitly specify the table name

    protected $fillable = [
        'name',
        'email',
        'review',
        'rating',
        'image',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope for filtering by rating
     */
    public function scopeByRating($query, $rating = null)
    {
        if ($rating) {
            if (is_array($rating)) {
                return $query->whereIn('rating', $rating);
            }
            return $query->where('rating', $rating);
        }
        return $query;
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeByDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            return $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            return $query->where('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Scope for searching in name, email, or review
     */
    public function scopeSearch($query, $searchTerm)
    {
        if ($searchTerm) {
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('review', 'like', '%' . $searchTerm . '%');
            });
        }
        return $query;
    }

    /**
     * Get reviews with specific star rating
     */
    public static function getReviewsByRating($rating)
    {
        return self::where('rating', $rating)->get();
    }

    /**
     * Get average rating
     */
    public static function getAverageRating()
    {
        return self::avg('rating');
    }

    /**
     * Get rating distribution
     */
    public static function getRatingDistribution()
    {
        return self::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->keyBy('rating');
    }

    /**
     * Get total count of reviews
     */
    public static function getTotalCount()
    {
        return self::count();
    }

    /**
     * Get positive reviews count (4-5 stars)
     */
    public static function getPositiveCount()
    {
        return self::whereIn('rating', [4, 5])->count();
    }

    /**
     * Get negative reviews count (1-2 stars)
     */
    public static function getNegativeCount()
    {
        return self::whereIn('rating', [1, 2])->count();
    }

    /**
     * Get neutral reviews count (3 stars)
     */
    public static function getNeutralCount()
    {
        return self::where('rating', 3)->count();
    }

    /**
     * Get recent reviews (last 30 days)
     */
    public static function getRecentCount()
    {
        return self::where('created_at', '>=', now()->subDays(30))->count();
    }

    /**
     * Get monthly trend data
     */
    public static function getMonthlyTrends($startDate = null, $endDate = null)
    {
        $query = self::query();
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        return $query->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total,
                AVG(rating) as average_rating
            ')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Get review statistics with optional filters
     */
    public static function getStatistics($filters = [])
    {
        $query = self::query();
        
        // Apply the same filters as in controller
        if (!empty($filters['rating']) && $filters['rating'] !== 'all') {
            if ($filters['rating'] === '4-5') {
                $query->whereIn('rating', [4, 5]);
            } elseif ($filters['rating'] === '1-3') {
                $query->whereIn('rating', [1, 2, 3]);
            } else {
                $query->where('rating', $filters['rating']);
            }
        }
        
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('review', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        // Calculate total reviews
        $totalReviews = $query->count();
        
        // Calculate average rating
        $averageRating = $totalReviews > 0 ? $query->avg('rating') : 0;
        
        // Calculate rating distribution
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = (clone $query)->where('rating', $i)->count();
        }
        
        // Calculate positive reviews (4-5 stars)
        $positiveReviews = (clone $query)->whereIn('rating', [4, 5])->count();
        
        // Calculate negative reviews (1-2 stars)
        $negativeReviews = (clone $query)->whereIn('rating', [1, 2])->count();
        
        // Calculate neutral reviews (3 stars)
        $neutralReviews = (clone $query)->where('rating', 3)->count();
        
        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 1),
            'positive_reviews' => $positiveReviews,
            'negative_reviews' => $negativeReviews,
            'neutral_reviews' => $neutralReviews,
            'rating_distribution' => $ratingDistribution,
        ];
    }
}