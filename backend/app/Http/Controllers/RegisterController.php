<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|in:client,lessor',
            'country' => 'required|string',
            'city' => 'required|string',
            'cin' => 'required|digits:8|unique:users',
            'phone' => 'required|string|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, 
            'role' => $request->role,
            'country'  => $request->country,
            'city'     => $request->city,
            'cin'      => $request->cin,
            'phone'    => $request->phone,
        ]);
        

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'message' => "User successfully registered",
            'token' => $token,
            'user' => $user,
        ]);
    }
}
