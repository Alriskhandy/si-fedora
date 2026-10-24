<?php

/*
|--------------------------------------------------------------------------
| Route Web
|--------------------------------------------------------------------------
|
| Pintu masuk route web. Route aplikasi dipecah per area bisnis di folder
| routes/web/, mengikuti struktur folder app/Http/Controllers:
|
|   web/umum.php         dashboard, profil, notifikasi, berkas, arsip
|   web/admin.php        role & permission, activity log, pengguna, tim, modul
|   web/master-data.php  kabupaten/kota dan seluruh master data
|   web/jadwal.php       jadwal, penetapan jadwal, undangan, surat pemberitahuan
|   web/fasilitasi.php   permohonan dan tahapan 1–7, perpanjangan waktu
|
| Route autentikasi (login, logout, verifikasi) ada di routes/auth.php.
|
| Aturan:
| - Hak akses route memakai middleware `can:<permission>` (katalog di
|   config/permissions.php), bukan nama role.
| - Route statis (mis. /create) didaftarkan SEBELUM route ber-parameter
|   (mis. /{id}) dengan prefix yang sama.
| - Setelah menambah/mengubah route, perbarui snapshot akses:
|   UPDATE_ROUTE_SNAPSHOT=1 php artisan test --filter=RouteAuthorizationTest
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman awal: login untuk tamu, dashboard untuk pengguna yang sudah login.
// Pengguna yang belum login diarahkan ke sini (redirectGuestsTo di bootstrap/app.php).
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
})->name('home');

// Seluruh fitur aplikasi: wajib login dan nomor WhatsApp terverifikasi.
Route::middleware(['auth', 'verified.phone'])->group(function () {
    require __DIR__.'/web/umum.php';
    require __DIR__.'/web/admin.php';
    require __DIR__.'/web/master-data.php';
    require __DIR__.'/web/jadwal.php';
    require __DIR__.'/web/fasilitasi.php';
});
