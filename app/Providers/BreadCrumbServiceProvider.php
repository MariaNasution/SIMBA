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
        // Defer the View::composer registration until after all service providers are booted
        $this->app->booted(function () {
            // Target the specific navbar component
            View::composer('components.navbar', function ($view) {
                $breadcrumbService = app(BreadCrumbService::class);

                // Pass route parameters to the breadcrumb service
                $params = Route::current()->parameters();
                $breadcrumbs = $breadcrumbService->generateBreadcrumbs($params);

                $view->with('breadcrumbs', $breadcrumbs);
            });
        });
    }
}