<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LessorController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if ($request->user()->role !== 'lessor') {
            return response()->json(['message' => 'Forbidden, only lessors can access this resource'], 403);
        }

        // Proceed with your logic for lessors
        return response()->json();
    }
    
}
