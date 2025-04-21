<?php

namespace App\Providers;

use App\Models\Perwalian;
use Illuminate\Support\ServiceProvider;
use App\Models\StudentBehavior;
use App\Observers\StudentBehaviorObserver;
use App\Models\RequestKonseling;
use App\Observers\PerwalianObserver;
use App\Observers\RequestKonselingObserver;
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
        StudentBehavior::observe(StudentBehaviorObserver::class);
        RequestKonseling::observe(RequestKonselingObserver::class);
        // Register PerwalianObserver with NotificationService dependency
        Perwalian::observe(function () {
            return new PerwalianObserver(app(NotificationService::class));
        });
    }
}