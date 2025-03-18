<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return new ProfileResource($user);
        }
        
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }
}
