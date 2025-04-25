<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Services\FirebaseNotificationService;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Firebase Messaging
        $this->app->singleton('firebase.messaging', function ($app) {
            return (new Factory)
                ->withServiceAccount(storage_path('app/firebase-credentials.json'))
                ->createMessaging();
        });

        // Register Firebase Notification Service
        $this->app->singleton(FirebaseNotificationService::class, function ($app) {
            return new FirebaseNotificationService();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/firebase.php' => config_path('firebase.php'),
        ], 'firebase-config');
    }
} 