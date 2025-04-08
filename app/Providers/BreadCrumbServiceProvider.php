<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\BreadCrumbService;
use Illuminate\Support\Facades\Route;

class BreadCrumbServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BreadCrumbService::class, function ($app) {
            return new BreadCrumbService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            View::composer('components.navbar', function ($view) {
                $breadcrumbService = app(BreadCrumbService::class);
                $params = Route::current()->parameters();
                $breadcrumbs = $breadcrumbService->generateBreadcrumbs($params);
                $notifications = $breadcrumbService->generateNotifications();

                // Debug output

                $view->with([
                    'breadcrumbs' => $breadcrumbs,
                    'notifications' => $notifications
                ]);
            });
        });
    }
}