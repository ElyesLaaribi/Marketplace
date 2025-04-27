<?php

namespace App\Console\Commands;

use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRentalReminders extends Command
{
    protected $signature = 'rentals:check-reminders';
    protected $description = 'Check for upcoming rentals and send notifications';

    public function handle()
    {
        Log::info('Starting rental reminder check at ' . now()->format('Y-m-d H:i:s'));
        $this->info('Checking for upcoming rentals...');

        // Get rentals starting in the next 24 hours
        $upcomingRentals = Rental::where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDay())
            ->where('status', 'active')
            ->with(['user', 'listing'])
            ->get();

        Log::info('Found ' . $upcomingRentals->count() . ' upcoming rentals', [
            'current_time' => now()->format('Y-m-d H:i:s'),
            'rentals' => $upcomingRentals->map(function($rental) {
                return [
                    'id' => $rental->id,
                    'start_date' => $rental->start_date,
                    'user_id' => $rental->user_id,
                    'status' => $rental->status
                ];
            })->toArray()
        ]);

        $this->info('Found ' . $upcomingRentals->count() . ' upcoming rentals');

        foreach ($upcomingRentals as $rental) {
            try {
                Log::info('Processing rental #' . $rental->id, [
                    'start_date' => $rental->start_date,
                    'user_id' => $rental->user_id,
                    'device_token' => $rental->user->device_token,
                    'time_until_start' => now()->diffInHours($rental->start_date) . ' hours'
                ]);

                // Send notification to the user
                $this->sendReminderNotification($rental);
                
                $this->info("Reminder sent for rental #{$rental->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send reminder for rental #{$rental->id}: " . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error("Failed to send reminder for rental #{$rental->id}");
            }
        }

        $this->info('Reminder check completed at ' . now()->format('Y-m-d H:i:s'));
    }

    private function sendReminderNotification($rental)
    {
        $user = $rental->user;
        $listing = $rental->listing;

        if (!$user->device_token) {
            Log::warning('No device token found for user', [
                'user_id' => $user->id,
                'rental_id' => $rental->id
            ]);
            return;
        }

        // Format the start date
        $startDate = Carbon::parse($rental->start_date)->format('F j, Y');

        // Prepare notification data
        $notificationData = [
            'title' => 'Upcoming Rental Reminder',
            'body' => "Your rental for {$listing->title} starts on {$startDate}",
            'rental_id' => $rental->id,
            'listing_name' => $listing->title,
            'start_date' => $rental->start_date,
            'type' => 'rental_reminder'
        ];

        Log::info('Sending notification', [
            'user_id' => $user->id,
            'device_token' => $user->device_token,
            'notification_data' => $notificationData,
            'time_until_start' => now()->diffInHours($rental->start_date) . ' hours'
        ]);

        // Send notification using Firebase
        $this->sendFirebaseNotification($user, $notificationData);
    }

    private function sendFirebaseNotification($user, $data)
    {
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            
            $firebaseService->sendNotification(
                $user->device_token,
                $data['title'],
                $data['body'],
                [
                    'rental_id' => $data['rental_id'],
                    'listing_name' => $data['listing_name'],
                    'start_date' => $data['start_date'],
                    'type' => $data['type'],
                    'user_id' => (string) $user->id
                ]
            );

            Log::info('Firebase notification sent successfully', [
                'user_id' => $user->id,
                'device_token' => $user->device_token,
                'notification_data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Firebase notification: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id,
                'device_token' => $user->device_token,
                'notification_data' => $data
            ]);
            throw $e;
        }
    }
} 