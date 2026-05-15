<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CordonAppointmentCountsController extends Controller
{
    public function index()
    {
        return view('cordon.dashboard');
    }
}