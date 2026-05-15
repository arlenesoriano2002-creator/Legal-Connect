<?php

namespace App\Http\Controllers;

use DB;

class DebugJsonController extends Controller
{
    public function getInquiriesJson()
    {
        $inquiries = DB::table('concerns_inquiries_message')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'count' => $inquiries->count(),
            'first_record' => $inquiries->first(),
            'first_three_records' => $inquiries->take(3),
            'all_records_subjects' => $inquiries->map(function($r) { 
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'subject' => $r->subject,
                    'subject_empty' => empty($r->subject),
                    'subject_null' => is_null($r->subject),
                    'subject_length' => strlen($r->subject ?? '')
                ];
            })
        ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }
}
