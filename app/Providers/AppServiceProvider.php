<?php

namespace App\Providers;

use App\Models\Perwalian;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use App\Models\StudentBehavior;
use App\Observers\StudentBehaviorObserver;
use App\Models\RequestKonseling;
use App\Observers\PerwalianObserver;
use App\Observers\RequestKonselingObserver;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;


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
        User::observe(UserObserver::class);
        if (app()->environment('remote') || env('FORCE_HTTPS',false)) {
            URL::forceScheme('https');
        }
        // app(\App\Http\Controllers\AuthController::class)->getExternalApiToken();
        Paginator::useBootstrapFive();

    }
}