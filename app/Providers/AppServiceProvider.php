<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\StudentBehavior;
use App\Observers\StudentBehaviorObserver;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Resolve the NotificationService instance from the container
        $notificationService = $this->app->make(NotificationService::class);
        
        // Register the observer for StudentBehavior
        StudentBehavior::observe(new StudentBehaviorObserver($notificationService));
    }
}
