<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;

class SendRentalReminders extends Command
{
    protected $signature = 'send:rental-reminders';
    protected $description = 'Send notifications for rentals starting in 24 hours';

    public function handle(FirebaseNotificationService $firebaseService)
    {
        $now = Carbon::now();
        $targetTime = $now->copy()->addDay();

        $rentals = Reservation::with(['user', 'listing'])
            ->whereBetween('start_date', [
                $now->format('Y-m-d H:i:s'),
                $targetTime->format('Y-m-d H:i:s')
            ])
            ->where('reminder_sent', false)
            ->get();

        foreach ($rentals as $rental) {
            if ($token = $rental->user->device_token) {
                $startDate = $rental->start_date->setTimezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s');

                $data = [
                    'type' => 'rental_reminder',
                    'rental_id' => (string) $rental->id,
                    'listing_name' => $rental->listing->name,
                    'start_date' => $startDate,
                    'message' => "Your rental for {$rental->listing->name} starts tomorrow!" 
                ];

                $firebaseService->sendNotification(
                    $token,
                    'Upcoming Rental',
                    $data['message'],
                    $data
                );

                $rental->update([
                    'reminder_sent' => true,
                    'reminder_sent_at' => Carbon::now(),
                ]);
            }
        }

        $this->info('Reminder notifications sent: ' . $rentals->count());
    }
}