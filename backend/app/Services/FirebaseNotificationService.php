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
        try {
            $this->messaging = app('firebase.messaging');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase Messaging', [
                'error' => $e->getMessage()
            ]);
            $this->messaging = null;
        }
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        // Validate input parameters
        if (empty($token) || empty($title) || empty($body)) {
            Log::warning('Invalid notification parameters', [
                'token' => $token,
                'title' => $title,
                'body' => $body
            ]);
            throw new \InvalidArgumentException('Token, title, and body are required');
        }

        // Validate Firebase initialization
        if (!$this->messaging) {
            Log::error('Firebase messaging not initialized');
            throw new \RuntimeException('Firebase messaging not initialized');
        }

        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);
            
            $result = $this->messaging->send($message);
            
            Log::info('Notification sent successfully', [
                'token' => substr($token, 0, 10) . '...',  // Only log part of the token for security
                'title' => $title,
                'body' => $body
            ]);
            
            return $result;
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            Log::warning('Device token not found in Firebase', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::warning('Invalid message format', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 10) . '...',
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 10) . '...',
                'title' => $title,
                'body' => $body
            ]);
            throw $e;
        }
    }
    
    public function sendMulticastNotification($tokens, $title, $body, $data = [])
    {
        // Validate input parameters
        if (empty($tokens) || !is_array($tokens) || empty($title) || empty($body)) {
            Log::warning('Invalid multicast notification parameters', [
                'tokens_count' => is_array($tokens) ? count($tokens) : 0,
                'title' => $title,
                'body' => $body
            ]);
            throw new \InvalidArgumentException('Tokens (array), title, and body are required');
        }

        // Validate Firebase initialization
        if (!$this->messaging) {
            Log::error('Firebase messaging not initialized');
            throw new \RuntimeException('Firebase messaging not initialized');
        }

        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);
            
            $result = $this->messaging->sendMulticast($message, $tokens);
            
            Log::info('Multicast notification sent successfully', [
                'tokens_count' => count($tokens),
                'title' => $title,
                'body' => $body,
                'success_count' => $result->successes()->count(),
                'failure_count' => $result->failures()->count()
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send multicast notification', [
                'error' => $e->getMessage(),
                'tokens_count' => count($tokens),
                'title' => $title,
                'body' => $body
            ]);
            throw $e;
        }
    }
}