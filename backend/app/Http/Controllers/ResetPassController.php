<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Hashing\Hasher;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Validation\ValidationException;
use App\Mail\ResetPasswordMail;

class ResetPassController extends Controller
{
    /**
     * Generate a password reset token.
     *
     * @param  ForgotPasswordRequest  $request
     * @param  Hasher  $hasher
     * @return JsonResponse
     * @throws ValidationException
     */
    public function forgot(Hasher $hasher, ForgotPasswordRequest $request): JsonResponse
    {
        // Retrieve the user using the provided email address
        $user = User::where('email', $request->input('email'))->first();

        // If no user exists, return an error response
        if (!$user) {
            return response()->json([
                'error'   => 'No record found',
                'message' => 'Incorrect email address provided'
            ], 404);
        }

        // Generate a 4-digit random token (padded with zeros if necessary)
        $resetPasswordToken = str_pad(random_int(1, 999), 4, '0', STR_PAD_LEFT);

        // Retrieve the password reset record if exists
        $passwordReset = PasswordReset::where('email', $user->email)->first();

        if (!$passwordReset) {
            // Create a new password reset record
            PasswordReset::create([
                'email' => $user->email,
                'token' => $resetPasswordToken,
            ]);
        } else {
            // Update the existing record with a new token
            $passwordReset->update([
                'email' => $user->email,
                'token' => $resetPasswordToken,
            ]);
        }
        Mail::to($user->email)->send(new ResetPasswordMail($resetPasswordToken));

        // Return a success response with the reset token (for demonstration; avoid sending tokens in production)
        return response()->json([
            'message' => 'A code has been sent to your email address'
        ], 200);
    }

    /**
     * Reset the user's password.
     *
     * @param  ResetPasswordRequest  $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        // Validate the request data
        $attributes = $request->validated();

        // Retrieve the user using the provided email address
        $user = User::where('email', $attributes['email'])->first();

        // If the user is not found, return an error response
        if (!$user) {
            return response()->json([
                'error'   => 'No record found',
                'message' => 'Incorrect email address provided'
            ], 404);
        }

        // Retrieve the password reset request for the user
        $resetRequest = PasswordReset::where('email', $user->email)->first();

        // If no reset request exists or the token does not match, return an error
        if (!$resetRequest || $resetRequest->token != $request->token) {
            return response()->json([
                'error'   => 'Token mismatch',
                'message' => 'An error occurred. Please try again later'
            ], 400);
        }

        // Update the user's password
        $user->fill([
            'password' => Hash::make($attributes['password']),
        ]);
        $user->save();

        // Delete any existing tokens (e.g., for API authentication)
        $user->tokens()->delete();

        // Delete the password reset record
        $resetRequest->delete();

        // Return a success response
        return response()->json([
            'message' => 'Password reset successfully'
        ], 200);
    }
}
