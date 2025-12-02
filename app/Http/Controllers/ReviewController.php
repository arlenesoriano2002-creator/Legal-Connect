<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Display testimonial page with recent feedbacks.
     */
    public function index()
    {
        // Get all reviews (latest first)
        $reviews = Review::orderBy('created_at', 'desc')->get();

        // Compute average rating and total count
        $averageRating = Review::avg('rating');
        $totalReviews  = Review::count();

        return view('testimonial', compact('reviews', 'averageRating', 'totalReviews'));
    }

    /**
     * Store client feedback (only if logged in).
     */
    public function store(Request $request)
        {
            // Check if user is not logged in
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'You must log in first to submit feedback.'
                ], 401);
            }

            $request->validate([
                'review' => 'required|string|max:1000',
                'rating' => 'required|integer|min:1|max:5',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $review = new Review();
            $review->name = Auth::user()->name;
            $review->email = Auth::user()->email;
            $review->review = $request->review;
            $review->rating = $request->rating;

            if ($request->hasFile('image')) {
                $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move(public_path('uploads/reviews'), $fileName);
                $review->image = $fileName;
            }

            $review->save();

            return response()->json([
                'success' => 'Thank you! Your feedback has been submitted.'
            ]);
        }

}
