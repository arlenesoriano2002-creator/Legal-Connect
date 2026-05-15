<?php

namespace App\Http\Controllers;

use App\Models\FeedbackReview;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedbackReportsController extends Controller
{
    /**
     * Display feedback reports dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'rating' => $request->input('rating'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'search' => $request->input('search')
        ];

        // Start query
        $query = FeedbackReview::query();

        // Apply rating filter
        if ($filters['rating'] && $filters['rating'] !== 'all') {
            if ($filters['rating'] === '4-5') {
                $query->whereIn('rating', [4, 5]);
            } elseif ($filters['rating'] === '1-3') {
                $query->whereIn('rating', [1, 2, 3]);
            } else {
                $query->where('rating', $filters['rating']);
            }
        }

        // Apply date range filter
        if ($filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Apply search filter
        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('review', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Get paginated results
        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics using the new model's method
        $stats = FeedbackReview::getStatistics($filters);

         return view('staff.feedbackReports', compact('reviews', 'stats', 'filters'));
    }

    /**
     * Generate and download PDF report
     */
    public function generatePdf(Request $request)
    {
        // Get filter parameters
        $filters = [
            'rating' => $request->input('rating'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'search' => $request->input('search')
        ];

        // Start query
        $query = FeedbackReview::query();

        // Apply filters
        if ($filters['rating'] && $filters['rating'] !== 'all') {
            if ($filters['rating'] === '4-5') {
                $query->whereIn('rating', [4, 5]);
            } elseif ($filters['rating'] === '1-3') {
                $query->whereIn('rating', [1, 2, 3]);
            } else {
                $query->where('rating', $filters['rating']);
            }
        }

        if ($filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('review', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Get all results for PDF
        $reviews = $query->orderBy('created_at', 'desc')->get();
        $stats = FeedbackReview::getStatistics($filters);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.feedback-report', [
            'reviews' => $reviews,
            'stats' => $stats,
            'filters' => [
                'rating' => $filters['rating'],
                'startDate' => $filters['start_date'],
                'endDate' => $filters['end_date'],
                'search' => $filters['search'],
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]
        ]);

        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        // Generate filename
        $filename = 'feedback-report-' . date('Y-m-d-H-i-s') . '.pdf';

        // Return PDF for inline viewing (stream) so browsers can open it in a new tab
        return $pdf->stream($filename);
    }

    /**
     * Get feedback data for charts (JSON API)
     */
    public function getChartData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = FeedbackReview::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Monthly trend data
        $monthlyTrends = $query->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total,
                AVG(rating) as average_rating
            ')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Rating distribution
        $ratingDistribution = $query->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        return response()->json([
            'monthly_trends' => $monthlyTrends,
            'rating_distribution' => $ratingDistribution,
            'success' => true
        ]);
    }

    /**
     * Export reviews as CSV
     */
    public function exportCsv(Request $request)
    {
        // Get filter parameters
        $filters = [
            'rating' => $request->input('rating'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'search' => $request->input('search')
        ];

        // Start query
        $query = FeedbackReview::query();

        // Apply filters
        if ($filters['rating'] && $filters['rating'] !== 'all') {
            if ($filters['rating'] === '4-5') {
                $query->whereIn('rating', [4, 5]);
            } elseif ($filters['rating'] === '1-3') {
                $query->whereIn('rating', [1, 2, 3]);
            } else {
                $query->where('rating', $filters['rating']);
            }
        }

        if ($filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('review', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Get all results
        $reviews = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'feedback-export-' . date('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reviews) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Add headers
            fputcsv($file, ['Name', 'Email', 'Rating', 'Review', 'Date']);

            // Add data
            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->name,
                    $review->email,
                    $review->rating,
                    $review->review,
                    $review->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}