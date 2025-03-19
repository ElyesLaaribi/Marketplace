<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ProfileResource;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;

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

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $data = array_filter($request->only(['name','email','phone','cin','country','city']));
        if (empty($data)) {
            return response()->json(['message' => 'No changes provided'], 400);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,    
        ]);
    }
}
