<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout halaman tamu (`<x-guest-layout>`) bawaan Breeze, dipakai halaman konfirmasi kata sandi dan verifikasi email.
 */
class GuestLayout extends Component
{
    /**
     * View layout tamu.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
