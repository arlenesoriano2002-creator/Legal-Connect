<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CordonChoicePurposeController extends Controller
{
    public function index()
    {
        return response()->json([]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Created']);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Deleted']);
    }
}