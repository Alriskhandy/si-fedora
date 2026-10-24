<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Login dan logout. Login dibatasi 5 percobaan per email+IP (LoginRequest); login/logout dicatat di activity log.
 *
 * Akses: tamu (login), pengguna login (logout).
 * View: resources/views/auth
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login, lalu arahkan ke halaman tujuan sebelumnya atau dashboard.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['ip_address' => $request->ip()])
            ->log('login');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Logout dan akhiri sesi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($user) {
            activity()
                ->causedBy($user)
                ->withProperties(['ip_address' => $request->ip()])
                ->log('logout');
        }

        return redirect('/');
    }
}
