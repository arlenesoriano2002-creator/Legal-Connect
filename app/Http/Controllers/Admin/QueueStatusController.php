<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class QueueStatusController extends Controller
{
    public function index(Request $request)
    {
        $last = DB::table('email_fetch_logs')->orderBy('ran_at', 'desc')->first();

        return response()->json([
            'last' => $last,
        ]);
    }
}
