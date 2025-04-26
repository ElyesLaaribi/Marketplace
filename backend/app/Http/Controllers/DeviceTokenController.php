<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    // In UserController.php or similar
    public function updateDeviceToken(Request $request)
    {
        try {
            $request->validate([
                'device_token' => 'required|string'
            ]);
            
            $user = Auth::user();
            
            if (!$user) {
                Log::error('Device token update failed: No authenticated user');
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            Log::info('Updating device token for user', [
                'user_id' => $user->id,
                'token_first_chars' => substr($request->device_token, 0, 10) . '...'
            ]);
            
            $user->device_token = $request->device_token;
            $user->save();
            
            Log::info('Device token updated successfully for user ' . $user->id);
            
            return response()->json([
                'message' => 'Device token updated successfully',
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating device token', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'not authenticated'
            ]);
            
            return response()->json([
                'error' => 'Failed to update device token: ' . $e->getMessage()
            ], 500);
        }
    }
}
