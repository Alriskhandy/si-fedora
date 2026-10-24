<?php

namespace App\Providers;

use App\View\Composers\NavbarComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Konfigurasi umum aplikasi: Gate tambahan, paginasi Bootstrap, dan view composer.
 *
 * Policy tidak didaftarkan di sini: Laravel menemukannya otomatis berdasarkan nama
 * (App\Models\X → App\Policies\XPolicy).
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan aplikasi.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inisialisasi layanan aplikasi.
     */
    public function boot(): void
    {
        // Log Viewer (opcodesio/log-viewer) hanya untuk pemegang permission log-viewer.view
        Gate::define('viewLogViewer', fn ($user) => $user->can('log-viewer.view'));

        Paginator::useBootstrapFive();

        View::composer('layouts.partials.navbar', NavbarComposer::class);
    }
}
