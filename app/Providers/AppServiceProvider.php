<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\StudentBehavior;
use App\Observers\StudentBehaviorObserver;
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
    }
}