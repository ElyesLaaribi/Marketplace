<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseNotificationService;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendTest(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            if (empty($user->device_token)) {
                return response()->json([
                    'error' => 'No device token found',
                    'user_id' => $user->id
                ], 400);
            }
            
            Log::info('Sending test notification to user', [
                'user_id' => $user->id,
                'token_first_chars' => substr($user->device_token, 0, 10) . '...'
            ]);
            
            $data = [
                'type' => 'test_notification',
                'message' => 'This is a test notification!',
                'timestamp' => now()->toIso8601String()
            ];
            
            $this->firebaseService->sendNotification(
                $user->device_token,
                'Test Notification',
                'This is a test notification to verify your setup works correctly.',
                $data
            );
            
            return response()->json([
                'message' => 'Test notification sent successfully',
                'user_id' => $user->id,
                'token_length' => strlen($user->device_token)
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending test notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'not authenticated'
            ]);
            
            return response()->json([
                'error' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testDirectFCM(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user || empty($user->device_token)) {
                return response()->json(['error' => 'User not authenticated or no device token'], 400);
            }
            
            $serverKey = config('services.firebase.server_key');
            
            if (empty($serverKey)) {
                return response()->json(['error' => 'Firebase server key not configured'], 500);
            }
            
            $data = [
                'to' => $user->device_token,
                'notification' => [
                    'title' => 'Direct FCM Test',
                    'body' => 'Testing direct FCM API call',
                    'icon' => '/favicon.ico',
                    'click_action' => 'http://localhost:5173'
                ],
                'data' => [
                    'type' => 'test_direct',
                    'message' => 'This is a direct FCM test',
                    'timestamp' => now()->toIso8601String()
                ]
            ];
            
            $headers = [
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            $result = curl_exec($ch);
            
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('CURL error: ' . $error);
            }
            
            curl_close($ch);
            $response = json_decode($result, true);
            
            Log::info('Direct FCM response', ['response' => $response]);
            
            return response()->json([
                'message' => 'Direct FCM test sent',
                'fcm_response' => $response
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in direct FCM test', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Direct FCM test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}