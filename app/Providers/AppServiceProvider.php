<?php

namespace App\Providers;

use App\Models\Perwalian;
use Illuminate\Support\ServiceProvider;
use App\Models\StudentBehavior;
use App\Observers\StudentBehaviorObserver;
use App\Models\RequestKonseling;
use App\Observers\PerwalianObserver;
use App\Observers\RequestKonselingObserver;

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
        Perwalian::observe(PerwalianObserver::class);
    }
}