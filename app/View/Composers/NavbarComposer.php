<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notifikasi;

class NavbarComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $unreadNotifications = Notifikasi::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();

            $view->with('unreadNotifications', $unreadNotifications);
        } else {
            $view->with('unreadNotifications', 0);
        }
    }
}
