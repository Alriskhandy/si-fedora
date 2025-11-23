<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
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
        Blade::if('hastemporary', function ($role) {
            return auth()->check() && 
                   auth()->user()->activeTemporaryRoles()->whereHas('role', function($q) use ($role) { 
                       $q->where('name', $role); 
                   })->exists();
        });
    }
}
