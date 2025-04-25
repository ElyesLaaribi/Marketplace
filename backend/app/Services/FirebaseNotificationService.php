<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);
            
            $result = $this->messaging->send($message);
            
            Log::info('Notification sent successfully', [
                'token' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'error' => $e->getMessage(),
                'token' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);
            throw $e;
        }
    }
    
    public function sendMulticastNotification($tokens, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);
            
            $result = $this->messaging->sendMulticast($message, $tokens);
            
            Log::info('Multicast notification sent successfully', [
                'tokens' => $tokens,
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send multicast notification', [
                'error' => $e->getMessage(),
                'tokens' => $tokens,
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);
            throw $e;
        }
    }
}