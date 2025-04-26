<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Listing;
use Carbon\Carbon;

class CreateTestRental extends Command
{
    protected $signature = 'rental:create-test {--days=1 : Days ahead for rental to start} {--user_id= : Specific user ID to use}';
    protected $description = 'Create a test rental for notification testing';

    public function handle()
    {
        $daysAhead = $this->option('days');
        $userId = $this->option('user_id');
        
        try {
            // Find a user with a device token if not specified
            if (!$userId) {
                $user = User::whereNotNull('device_token')->first();
                if (!$user) {
                    $this->error('No user with device token found. Try updating a user\'s device token first.');
                    return 1;
                }
                $userId = $user->id;
            } else {
                $user = User::find($userId);
                if (!$user) {
                    $this->error("User with ID {$userId} not found.");
                    return 1;
                }
                
                if (!$user->device_token) {
                    $this->warn("Warning: User {$userId} does not have a device token set. Notifications may not work.");
                }
            }
            
            // Find a listing to use
            $listing = Listing::first();
            if (!$listing) {
                $this->error('No listings found in database.');
                return 1;
            }
            
            // Create start and end dates
            $startDate = Carbon::now()->addDays($daysAhead);
            $endDate = (clone $startDate)->addDays(7);
            
            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => $userId,
                'listing_id' => $listing->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $listing->price * 7,
                'reminder_sent' => false,
                'reminder_sent_at' => null
            ]);
            
            $this->info("Test rental created successfully!");
            $this->info("Rental ID: {$reservation->id}");
            $this->info("User ID: {$reservation->user_id}");
            $this->info("Listing: {$listing->name}");
            $this->info("Start Date: {$reservation->start_date->format('Y-m-d H:i:s')}");
            $this->info("End Date: {$reservation->end_date->format('Y-m-d H:i:s')}");
            $this->info("Device Token Present: " . ($user->device_token ? 'Yes' : 'No'));
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error creating test rental: {$e->getMessage()}");
            return 1;
        }
    }
} 