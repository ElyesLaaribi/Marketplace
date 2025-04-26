<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendRentalReminders extends Command
{
    protected $signature = 'send:rental-reminders {--force : Send notifications even if reminder_sent is true} {--days=1 : Days ahead to check for rentals}';
    protected $description = 'Send notifications for rentals starting within the specified days ahead';

    public function handle(FirebaseNotificationService $firebaseService)
    {
        $this->info('Checking for rental reminders...');
        
        try {
            $now = Carbon::now();
            $daysAhead = $this->option('days');
            $targetTime = $now->copy()->addDays($daysAhead);
            $force = $this->option('force');

            $query = Reservation::with(['user', 'listing'])
                ->whereBetween('start_date', [
                    $now->format('Y-m-d H:i:s'),
                    $targetTime->format('Y-m-d H:i:s')
                ]);
                
            if (!$force) {
                $query->where('reminder_sent', false);
            }
            
            $rentals = $query->get();

            if ($rentals->count() === 0) {
                $this->info('No rentals found needing notifications.');
                return 0;
            }
            
            $this->info('Found ' . $rentals->count() . ' rentals to send reminders for.');
            
            $successCount = 0;
            $failCount = 0;

            foreach ($rentals as $rental) {
                if (empty($rental->user) || empty($rental->user->device_token)) {
                    $this->warn("Skipping rental #{$rental->id} - No device token available");
                    continue;
                }
                
                try {
                    $token = $rental->user->device_token;
                    $startDate = $rental->start_date->setTimezone(config('app.timezone'))
                        ->format('Y-m-d H:i:s');

                    $data = [
                        'type' => 'rental_reminder',
                        'rental_id' => (string) $rental->id,
                        'listing_name' => $rental->listing ? $rental->listing->name : 'Your rental',
                        'start_date' => $startDate,
                        'message' => "Your rental" . ($rental->listing ? " for {$rental->listing->name}" : "") . " starts " . ($daysAhead == 1 ? "tomorrow" : "soon") . "!" 
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
                    
                    $successCount++;
                    $this->info("Sent reminder for rental #{$rental->id}");
                } catch (\Exception $e) {
                    $failCount++;
                    Log::error("Failed to send notification for rental #{$rental->id}", [
                        'user_id' => $rental->user->id,
                        'error' => $e->getMessage()
                    ]);
                    $this->error("Failed to send notification for rental #{$rental->id}");
                    
                    // Mark as sent anyway to avoid continuous retries that will fail
                    $rental->update([
                        'reminder_sent' => true,
                        'reminder_sent_at' => Carbon::now(),
                    ]);
                }
            }

            $this->info("Summary: {$successCount} sent successfully, {$failCount} failed");
            return 0;
        } catch (\Exception $e) {
            Log::error('Error in send:rental-reminders command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error("Failed: {$e->getMessage()}");
            return 1;
        }
    }
}