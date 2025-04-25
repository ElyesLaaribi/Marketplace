<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController extends Controller
{
    public function sendTest(Request $request)
    {
        try {
            // Get the authenticated user
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            // Get the user's device token
            $deviceToken = $user->device_token;
            
            if (!$deviceToken) {
                return response()->json(['error' => 'No device token found for this user'], 400);
            }
            
            // Create a notification
            $notification = Notification::create()
                ->withTitle('Test Notification')
                ->withBody('This is a test notification from your Laravel backend');
            
            // Create a message
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData(['type' => 'test']);
            
            // Get the Firebase Messaging service
            $messaging = app('firebase.messaging');
            
            // Send the message
            $result = $messaging->send($message);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification sent',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Firebase notification error: ' . $e->getMessage());
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}