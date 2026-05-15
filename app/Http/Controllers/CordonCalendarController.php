<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CordonCalendarController extends Controller
{
    public function index()
    {
        return view('cordon.calendar');
    }
}