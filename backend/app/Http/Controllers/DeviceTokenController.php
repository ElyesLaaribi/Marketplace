<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    // In UserController.php or similar
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);
        
        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();
        
        return response()->json(['message' => 'Device token updated successfully']);
    }
}
