<?php

namespace App\Providers;

use App\Models\PerpanjanganWaktu;
use App\Policies\PerpanjanganWaktuPolicy;
use App\View\Composers\NavbarComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    public function boot()
    {
        // Register policies
        Gate::policy(PerpanjanganWaktu::class, PerpanjanganWaktuPolicy::class);

        // Batasi akses Log Viewer untuk pemegang permission log-viewer.view
        Gate::define('viewLogViewer', function ($user) {
            return $user->can('log-viewer.view');
        });

        Paginator::useBootstrapFive();

        // Register view composer for navbar
        View::composer('partials.navbar', NavbarComposer::class);
    }
}
