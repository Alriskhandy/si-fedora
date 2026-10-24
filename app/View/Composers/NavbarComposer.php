<?php

namespace App\View\Composers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Menyediakan jumlah notifikasi belum dibaca (`$unreadNotifications`) untuk partial navbar.
 *
 * Didaftarkan untuk view `layouts.partials.navbar` di AppServiceProvider.
 */
class NavbarComposer
{
    /**
     * Tambahkan `$unreadNotifications` ke view (0 untuk tamu).
     */
    public function compose(View $view): void
    {
        $unreadNotifications = Auth::check()
            ? Notifikasi::where('user_id', Auth::id())->where('is_read', false)->count()
            : 0;

        $view->with('unreadNotifications', $unreadNotifications);
    }
}
