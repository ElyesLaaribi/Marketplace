<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Jobs\SendReservationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendRentalStartReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:rental-start-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users who have rentals starting tomorrow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Add a cache lock to prevent multiple runs within a short period
        // This ensures we don't send duplicate notifications if the command runs multiple times
        $lockKey = 'rental_notifications_lock';
        
        if (Cache::has($lockKey)) {
            $this->info("Command already ran recently. Skipping this execution.");
            return 0;
        }
        
        // Set a lock for 30 seconds to prevent duplicate runs
        Cache::put($lockKey, true, 30);
        
        $tomorrow = Carbon::tomorrow()->toDateString();
        
        $this->info("Looking for reservations starting on: {$tomorrow}");

        // Get all reservations starting tomorrow where reminder hasn't been sent yet
        // or was sent more than 12 hours ago (to allow for multiple reminders per day)
        $upcomingReservations = Reservation::with(['user', 'listing'])
            ->whereDate('start_date', $tomorrow)
            ->where(function($query) {
                $query->where('reminder_sent', false)
                    ->orWhereNull('reminder_sent')
                    ->orWhere('reminder_sent_at', '<', now()->subHours(12));
            })
            ->get();

        $this->info("Found {$upcomingReservations->count()} upcoming reservations needing reminders");

        $notificationCount = 0;

        foreach ($upcomingReservations as $reservation) {
            // Skip if user doesn't have a device token
            if (!$reservation->user || !$reservation->user->device_token) {
                $this->warn("User #{$reservation->user_id} doesn't have a device token. Skipping notification.");
                continue;
            }

            // Skip if listing doesn't exist 
            if (!$reservation->listing) {
                $this->warn("Listing #{$reservation->listing_id} not found. Skipping notification.");
                continue;
            }

            // Generate a unique cache key for this reservation notification
            $notificationKey = "notification_sent_reservation_{$reservation->id}_" . date('Y-m-d');
            
            // Check if we've already sent a notification for this reservation recently
            if (Cache::has($notificationKey)) {
                $this->info("Already sent notification for reservation #{$reservation->id} recently. Skipping.");
                continue;
            }
            
            $startDate = Carbon::parse($reservation->start_date)->format('F j, Y');
            $listingName = $reservation->listing->title ?? 'Your rental';
            
            try {
                // Dispatch notification job
                SendReservationNotification::dispatch(
                    $reservation->user->device_token,
                    "Rental Reminder",
                    "Your rental for {$listingName} is starting tomorrow!",
                    [
                        'rental_id' => $reservation->id,
                        'listing_id' => $reservation->listing_id,
                        'listing_name' => $listingName,
                        'start_date' => $reservation->start_date,
                        'url' => '/my-rentals'
                    ]
                );

                // Mark reminder as sent
                $reservation->reminder_sent = true;
                $reservation->reminder_sent_at = now();
                $reservation->save();

                // Cache that we've sent this notification to prevent duplicates
                Cache::put($notificationKey, true, 60 * 60); // Cache for 1 hour

                $notificationCount++;
                
                $this->info("Notification sent to user #{$reservation->user_id} for reservation #{$reservation->id}");
            } catch (\Exception $e) {
                Log::error('Failed to send rental reminder notification', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id,
                    'error' => $e->getMessage()
                ]);
                
                $this->error("Failed to send notification for reservation #{$reservation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$notificationCount} rental start reminder notifications");
        
        return 0;
    }
} 