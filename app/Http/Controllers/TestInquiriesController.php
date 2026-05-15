<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TestInquiriesController extends Controller
{
    public function test()
    {
        $inquiries = DB::table('concerns_inquiries_message')
            ->orderBy('created_at', 'desc')
            ->get();
        
        echo "<pre>";
        echo "Total inquiries: " . count($inquiries) . "\n\n";
        
        foreach ($inquiries as $idx => $inquiry) {
            echo "=== Inquiry $idx ===\n";
            echo "Type: " . gettype($inquiry) . "\n";
            if (is_object($inquiry)) {
                echo "Class: " . get_class($inquiry) . "\n";
                echo "Properties:\n";
                foreach (get_object_vars($inquiry) as $key => $value) {
                    echo "  - $key: " . (is_null($value) ? "NULL" : $value) . "\n";
                }
            }
            echo "\n";
            
            if ($idx >= 2) break; // Only show first 3
        }
        
        echo "</pre>";
    }
}
