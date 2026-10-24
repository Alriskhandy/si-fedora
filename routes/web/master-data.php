<?php

/*
|--------------------------------------------------------------------------
| Route Master Data
|--------------------------------------------------------------------------
|
| Data referensi: kabupaten/kota, tahapan, urusan, kelengkapan, jenis dokumen, bab.
| Controller: app/Http/Controllers/MasterData. Setiap resource dijaga permission-nya sendiri.
| Dimuat di dalam grup auth + verified.phone (routes/web.php).
|
*/

use App\Http\Controllers\MasterData\KabupatenKotaController;
use App\Http\Controllers\MasterData\MasterBabController;
use App\Http\Controllers\MasterData\MasterJenisDokumenController;
use App\Http\Controllers\MasterData\MasterKelengkapanController;
use App\Http\Controllers\MasterData\MasterTahapanController;
use App\Http\Controllers\MasterData\MasterUrusanController;
use Illuminate\Support\Facades\Route;

Route::resource('kabupaten-kota', KabupatenKotaController::class)
    ->parameters(['kabupaten-kota' => 'kabupatenKota'])
    ->middleware('can:master.kabkota.manage');

// Tahapan hanya bisa diubah; daftar 7 tahapan tetap
Route::resource('master-tahapan', MasterTahapanController::class)
    ->parameters(['master-tahapan' => 'masterTahapan'])
    ->only(['index', 'edit', 'update'])
    ->middleware('can:master.tahapan.manage');

Route::resource('master-urusan', MasterUrusanController::class)
    ->parameters(['master-urusan' => 'masterUrusan'])
    ->except(['show'])
    ->middleware('can:master.urusan.manage');

Route::resource('master-kelengkapan', MasterKelengkapanController::class)
    ->parameters(['master-kelengkapan' => 'masterKelengkapan'])
    ->except(['show'])
    ->middleware('can:master.kelengkapan.manage');

// Nama parameter `masterJenisDokuman` mengikuti nama variabel di controller
Route::middleware(['can:master.jenis-dokumen.manage'])->group(function () {
    Route::resource('master-jenis-dokumen', MasterJenisDokumenController::class)
        ->parameters(['master-jenis-dokumen' => 'masterJenisDokuman'])
        ->except(['show']);
    Route::post('/master-jenis-dokumen/{masterJenisDokuman}/toggle-status', [MasterJenisDokumenController::class, 'toggleStatus'])
        ->name('master-jenis-dokumen.toggle-status');
});

// Tambah & ubah memakai form di halaman daftar (store/update), tanpa halaman create/edit terpisah
Route::resource('master-bab', MasterBabController::class)
    ->parameters(['master-bab' => 'masterBab'])
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('can:master.bab.manage');
