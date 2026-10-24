<?php

/*
|--------------------------------------------------------------------------
| Route Autentikasi
|--------------------------------------------------------------------------
|
| Login, logout, verifikasi email, konfirmasi kata sandi, ganti kata sandi,
| dan verifikasi nomor WhatsApp (OTP). Controller: app/Http/Controllers/Auth.
|
| Dimuat di bootstrap/app.php (withRouting → then) dengan middleware `web`,
| SETELAH routes/web.php. Route di sini tidak memakai `verified.phone`, karena
| pengguna harus bisa memverifikasi nomornya sebelum mengakses fitur lain.
|
| Register dan reset kata sandi sengaja tidak tersedia; akun dibuat oleh Admin.
| Pembatasan percobaan: login 5×/email+IP (LoginRequest), OTP 5×/menit,
| kirim ulang OTP 3×/menit, verifikasi email 6×/menit.
|
*/

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Pengguna login (tanpa syarat nomor WhatsApp terverifikasi)
Route::middleware('auth')->group(function () {
    // Verifikasi email (bawaan Breeze)
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Konfirmasi & ganti kata sandi
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Verifikasi nomor WhatsApp dengan OTP (wajib sebelum fitur lain; lihat middleware EnsurePhoneIsVerified)
    Route::prefix('phone')->name('phone.')->group(function () {
        Route::get('verify', [PhoneVerificationController::class, 'showPhoneForm'])
            ->name('verify');

        Route::post('verify/send', [PhoneVerificationController::class, 'sendOtp'])
            ->middleware('throttle:5,1')
            ->name('verify.send');

        Route::get('verify/otp', [PhoneVerificationController::class, 'showOtpForm'])
            ->name('verify.otp.form');

        Route::post('verify/otp', [PhoneVerificationController::class, 'verifyOtp'])
            ->middleware('throttle:5,1')
            ->name('verify.otp');

        Route::post('verify/resend', [PhoneVerificationController::class, 'resendOtp'])
            ->middleware('throttle:3,1')
            ->name('verify.resend');
    });
});
