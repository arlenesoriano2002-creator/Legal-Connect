<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CordonPurposeVisitController extends Controller
{
    public function publicIndex()
    {
        return view('cordon.logbook');
    }

    public function index()
    {
        return view('cordon.index');
    }

    public function listJson()
    {
        return response()->json([]);
    }
}