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
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    if ($user->role !== 'lessor') {
        return response()->json(['message' => 'Forbidden, only lessors can access this resource'], 403);
    }

    return response()->json();
}

    
}
