<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Konfirmasi ulang kata sandi sebelum aksi sensitif (bawaan Breeze).
 *
 * Akses: pengguna login.
 * View: resources/views/auth
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Halaman konfirmasi kata sandi.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Periksa kata sandi, lalu lanjutkan ke halaman tujuan.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
