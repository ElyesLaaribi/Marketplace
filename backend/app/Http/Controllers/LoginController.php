<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }
    
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized, email not found',
                'errors'  => [
                    'email' => ['email not found']
                ]
            ], 422);
        }
    
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Unauthorized, incorrect password',
                'errors'  => [
                    'password' => ['The provided password is incorrect.']
                ]
            ], 422);
        }
    
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;
    
        return response()->json([
            'token' => $token,
            'user'  => $user,
            'role'  => $user->role
        ]);
    }
}
