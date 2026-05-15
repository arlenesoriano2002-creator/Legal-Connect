<?php

namespace App\Http\Controllers;

use DB;

class DiagnosticController extends Controller
{
    public function testQueryStructure()
    {
        $inquiries = DB::table('concerns_inquiries_message')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($inquiries->isEmpty()) {
            return "No records found in database.";
        }

        $output = "<h2>Query Diagnostic Report</h2>";
        $output .= "<p><strong>Total records:</strong> " . $inquiries->count() . "</p>";

        $first = $inquiries->first();
        $output .= "<h3>First Record Analysis:</h3>";
        $output .= "<p><strong>Object type:</strong> " . get_class($first) . "</p>";
        
        $output .= "<h4>All Properties:</h4>";
        $output .= "<ul>";
        foreach ((array)$first as $key => $value) {
            $displayValue = is_null($value) ? '<em>NULL</em>' : (strlen($value) > 100 ? substr($value, 0, 100) . '...' : htmlspecialchars($value));
            $output .= "<li><code>$key</code>: $displayValue</li>";
        }
        $output .= "</ul>";

        $output .= "<h4>Subject Property Test:</h4>";
        $output .= "<ul>";
        $output .= "<li>property_exists(\$first, 'subject'): " . (property_exists($first, 'subject') ? '<strong>TRUE</strong>' : '<strong>FALSE</strong>') . "</li>";
        $output .= "<li>\$first->subject value: <code>" . htmlspecialchars($first->subject ?? 'NULL') . "</code></li>";
        $output .= "<li>!empty(\$first->subject): " . (!empty($first->subject) ? '<strong>TRUE (condition passes)</strong>' : '<strong>FALSE (condition fails)</strong>') . "</li>";
        $output .= "<li>strlen(\$first->subject): " . strlen($first->subject ?? '') . "</li>";
        $output .= "</ul>";

        $output .= "<h4>First 5 Records - Subject Values Only:</h4>";
        $output .= "<table border='1' cellpadding='5'>";
        $output .= "<tr><th>ID</th><th>Name</th><th>Subject</th><th>Message (first 30)</th></tr>";
        foreach ($inquiries->take(5) as $inquiry) {
            $output .= "<tr>";
            $output .= "<td>" . htmlspecialchars($inquiry->id) . "</td>";
            $output .= "<td>" . htmlspecialchars($inquiry->name) . "</td>";
            $output .= "<td><code>" . htmlspecialchars($inquiry->subject ?? 'NULL') . "</code></td>";
            $output .= "<td>" . htmlspecialchars(substr($inquiry->message ?? '', 0, 30)) . "</td>";
            $output .= "</tr>";
        }
        $output .= "</table>";

        return $output;
    }
}
