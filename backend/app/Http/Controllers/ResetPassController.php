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
        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'error'   => 'No record found',
                'message' => 'Incorrect email address provided'
            ], 404);
        }

        $resetPasswordToken = str_pad(random_int(1, 999), 4, '0', STR_PAD_LEFT);

        $passwordReset = PasswordReset::where('email', $user->email)->first();

        if (!$passwordReset) {
            PasswordReset::create([
                'email' => $user->email,
                'token' => $resetPasswordToken,
            ]);
        } else {
            $passwordReset->update([
                'email' => $user->email,
                'token' => $resetPasswordToken,
            ]);
        }
        Mail::to($user->email)->send(new ResetPasswordMail($resetPasswordToken));

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
        $attributes = $request->validated();

        $user = User::where('email', $attributes['email'])->first();

        if (!$user) {
            return response()->json([
                'error'   => 'No record found',
                'message' => 'Incorrect email address provided'
            ], 404);
        }

        $resetRequest = PasswordReset::where('email', $user->email)->first();

        if (!$resetRequest || $resetRequest->token != $request->token) {
            return response()->json([
                'error'   => 'Token mismatch',
                'message' => 'An error occurred. Please try again later'
            ], 400);
        }

        if ($resetRequest->created_at->addMinutes(10)->isPast()) {
            $resetRequest->delete(); 
            return response()->json([
                'error'   => 'Token expired',
                'message' => 'The reset token has expired. Please request a new one.'
            ], 400);
        }

        $user->fill([
            'password' => Hash::make($attributes['password']),
        ]);
        $user->save();

        $user->tokens()->delete();

        $resetRequest->delete();

        return response()->json([
            'message' => 'Password reset successfully'
        ], 200);
    }
}
