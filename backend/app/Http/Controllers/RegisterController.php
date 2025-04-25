<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            Log::error('Registration validation failed', [
                'errors' => $validator->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'country' => $request->country,
                'city' => $request->city,
                'cin' => $request->cin,
                'phone' => $request->phone,
            ];

            Log::info('Attempting to create user', ['userData' => array_merge($userData, ['password' => 'HIDDEN'])]);

            $user = User::create($userData);

            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            Log::info('User successfully registered', ['userId' => $user->id, 'role' => $user->role]);

            return response()->json([
                'message' => "User successfully registered",
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
