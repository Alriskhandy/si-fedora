<?php

/*
|--------------------------------------------------------------------------
| Route Jadwal
|--------------------------------------------------------------------------
|
| Jadwal fasilitasi, penetapan jadwal (tahapan 3), undangan pelaksanaan, dan surat pemberitahuan.
| Controller: app/Http/Controllers/Jadwal.
| Dimuat di dalam grup auth + verified.phone (routes/web.php).
|
*/

use App\Http\Controllers\Jadwal\JadwalFasilitasiController;
use App\Http\Controllers\Jadwal\PenetapanJadwalController;
use App\Http\Controllers\Jadwal\SuratPemberitahuanController;
use App\Http\Controllers\Jadwal\UndanganPelaksanaanController;
use Illuminate\Support\Facades\Route;

// Jadwal fasilitasi: semua pengguna login dapat melihat; pengelolaan dengan jadwal.manage
Route::prefix('jadwal')->name('jadwal.')->controller(JadwalFasilitasiController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    Route::middleware(['can:jadwal.manage'])->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{jadwal}/edit', 'edit')->name('edit');
        Route::put('/{jadwal}', 'update')->name('update');
        Route::delete('/{jadwal}', 'destroy')->name('destroy');
    });

    Route::get('/{jadwal}', 'show')->name('show');
    Route::get('/{jadwal}/download', 'download')->name('download');
});

// Penetapan jadwal pelaksanaan per permohonan (tahapan 3)
Route::middleware(['can:penetapan-jadwal.manage'])->prefix('penetapan-jadwal')->name('penetapan-jadwal.')->controller(PenetapanJadwalController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{permohonan}/create', 'create')->name('create');
    Route::post('/{permohonan}', 'store')->name('store');
    Route::get('/{permohonan}', 'show')->name('show');
    Route::put('/{permohonan}', 'update')->name('update');
});

// Undangan pelaksanaan — sisi penerima (pemohon, verifikator, fasilitator)
Route::prefix('my-undangan')->name('my-undangan.')->controller(UndanganPelaksanaanController::class)->group(function () {
    Route::get('/', 'myUndangan')->name('index');
    Route::get('/{id}', 'view')->name('view');
});

// Undangan pelaksanaan — pengelolaan
Route::middleware(['can:undangan-pelaksanaan.manage'])->prefix('undangan-pelaksanaan')->name('undangan-pelaksanaan.')->controller(UndanganPelaksanaanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{permohonan}/create', 'create')->name('create');
    Route::post('/{permohonan}', 'store')->name('store');
    Route::get('/{permohonan}', 'show')->name('show');
    Route::post('/{permohonan}/update-file', 'updateFile')->name('update-file');
});

// Undangan pelaksanaan — tampilan khusus pemohon
Route::middleware(['can:undangan-pelaksanaan.view-own'])->prefix('pemohon/undangan')->name('pemohon.undangan.')->controller(UndanganPelaksanaanController::class)->group(function () {
    Route::get('/', 'myUndangan')->name('index');
    Route::get('/{id}', 'view')->name('view');
});

// Unduh file undangan (semua pengguna login)
Route::get('/undangan-pelaksanaan/{permohonan}/download', [UndanganPelaksanaanController::class, 'download'])
    ->name('undangan-pelaksanaan.download');

// Surat pemberitahuan jadwal ke kabupaten/kota
Route::middleware(['can:surat-pemberitahuan.manage'])->prefix('surat-pemberitahuan')->name('surat-pemberitahuan.')->controller(SuratPemberitahuanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{suratPemberitahuan}', 'show')->name('show');
    Route::get('/{suratPemberitahuan}/edit', 'edit')->name('edit');
    Route::put('/{suratPemberitahuan}', 'update')->name('update');
    Route::delete('/{suratPemberitahuan}', 'destroy')->name('destroy');
    Route::post('/{suratPemberitahuan}/send', 'send')->name('send');
    Route::get('/{suratPemberitahuan}/download', 'download')->name('download');
});
