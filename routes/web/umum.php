<?php

/*
|--------------------------------------------------------------------------
| Route Umum
|--------------------------------------------------------------------------
|
| Fitur yang dipakai semua pengguna yang login. Controller: app/Http/Controllers/Umum.
| Dimuat di dalam grup auth + verified.phone (routes/web.php).
|
*/

use App\Http\Controllers\Umum\ArsipController;
use App\Http\Controllers\Umum\BerkasController;
use App\Http\Controllers\Umum\DashboardController;
use App\Http\Controllers\Umum\NotifikasiController;
use App\Http\Controllers\Umum\ProfileController;
use Illuminate\Support\Facades\Route;

// Dashboard sesuai role pengguna
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profil akun sendiri
Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
    Route::get('/', 'edit')->name('edit');
    Route::patch('/', 'update')->name('update');
    Route::delete('/', 'destroy')->name('destroy');
});

// Notifikasi in-app milik pengguna
Route::prefix('notifikasi')->name('notifikasi.')->controller(NotifikasiController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::delete('/', 'destroyAll')->name('destroy-all');
    Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
});

// Dokumen privat (disk `dokumen`); hak akses diperiksa di controller lewat policy
Route::get('/berkas/{jenis}/{id}', [BerkasController::class, 'show'])
    ->whereNumber('id')
    ->name('berkas.show');

// Arsip dokumen; daftar dan detail dibatasi sesuai hak lihat permohonan
Route::prefix('arsip')->name('arsip.')->controller(ArsipController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/jenis/{jenisDokumenId}', 'listByJenis')->name('listByJenis');
    Route::get('/{permohonan}', 'show')->name('show');
});
