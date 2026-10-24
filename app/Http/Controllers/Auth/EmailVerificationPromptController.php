<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman pemberitahuan verifikasi email (bawaan Breeze).
 *
 * Akses: pengguna login.
 * View: resources/views/auth
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Tampilkan pemberitahuan verifikasi email, atau lanjut ke dashboard bila sudah terverifikasi.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
