<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FeedbackChartController extends Controller
{
    public function getFeedbackData()
    {
        // Count each rating from the reviews table
        $ratings = DB::table('reviews')
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')
            ->orderBy('rating', 'asc')
            ->get();

        // Prepare labels and data arrays
        $labels = ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'];
        $data = [0, 0, 0, 0, 0];

        foreach ($ratings as $rating) {
            $index = $rating->rating - 1;
            if (isset($data[$index])) {
                $data[$index] = $rating->total;
            }
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }
}
