<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FirebaseNotificationService;

class SendReservationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $deviceToken,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(FirebaseNotificationService $firebaseService)
    {
        try {
            $firebaseService->sendNotification(
                $this->deviceToken,
                $this->title,
                $this->body,
                array_merge($this->data, [
                    'type' => 'rental_reminder'
                ])
            );
        } catch (\Exception $e) {
            \Log::error("Notification failed: " . $e->getMessage());
            $this->fail($e);
        }
    }
}