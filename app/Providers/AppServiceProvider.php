<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\WhatsAppChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register WhatsApp Service as singleton
        $this->app->singleton(\App\Services\WhatsAppService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Register custom WhatsApp notification channel
        Notification::extend('whatsapp', function ($app) {
            return $app->make(WhatsAppChannel::class);
        });

        Blade::if('hastemporary', function ($role) {
            return auth()->check() &&
                auth()->user()->activeTemporaryRoles()->whereHas('role', function ($q) use ($role) {
                    $q->where('name', $role);
                })->exists();
        });

        Paginator::useBootstrapFive();
    }
}
