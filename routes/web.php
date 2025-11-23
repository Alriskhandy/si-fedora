<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
// use App\Http\Controllers\RoleController; 
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\JenisDokumenController; 
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\TahunAnggaranController; 
use App\Http\Controllers\JadwalFasilitasiController; 
use App\Http\Controllers\SuratPemberitahuanController; 
use App\Http\Controllers\TimPokjaController; 
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PermohonanDokumenController; 

// Route::middleware(['auth'])->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// });

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tambahin route untuk sementara
    // User Management
    Route::middleware(['role:superadmin|admin_peran'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
             ->name('users.reset-password');
    });
  // Role & Permission Management - hanya superadmin
  Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});
   // Jenis Dokumen Management - superadmin & admin_peran master data
   Route::middleware(['role:superadmin|admin_peran'])->group(function () {
    Route::resource('jenis-dokumen', JenisDokumenController::class);
    Route::resource('kabupaten-kota', KabupatenKotaController::class)->parameters([
        'kabupaten-kota' => 'kabupatenKota'
    ]);
    Route::resource('tahun-anggaran', TahunAnggaranController::class)->parameters([
        'tahun-anggaran' => 'tahunAnggaran'
    ]);
    Route::resource('tim-pokja', TimPokjaController::class)->parameters([
        'tim-pokja' => 'timPokja'
    ]);
});

// Jadwal Fasilitasi Management - admin_peran only
Route::middleware(['role:admin_peran'])->group(function () {
    Route::resource('jadwal', JadwalFasilitasiController::class)->parameters([
        'jadwal' => 'jadwal'
    ]);
    Route::post('/jadwal/{jadwal}/publish', [JadwalFasilitasiController::class, 'publish'])->name('jadwal.publish');
    Route::post('/jadwal/{jadwal}/cancel', [JadwalFasilitasiController::class, 'cancel'])->name('jadwal.cancel');
});

    // Surat Pemberitahuan Management - admin_peran only
    Route::middleware(['role:admin_peran'])->group(function () {
        Route::resource('surat-pemberitahuan', SuratPemberitahuanController::class)->parameters([
            'surat-pemberitahuan' => 'suratPemberitahuan'
        ]);
        Route::post('/surat-pemberitahuan/{suratPemberitahuan}/send', [SuratPemberitahuanController::class, 'send'])->name('surat-pemberitahuan.send');
        Route::get('/surat-pemberitahuan/{suratPemberitahuan}/download', [SuratPemberitahuanController::class, 'download'])->name('surat-pemberitahuan.download');
    });

        // Permohonan Management - role based
        Route::middleware(['role:kabkota|admin_peran'])->group(function () {
            Route::resource('permohonan', PermohonanController::class);
            Route::post('/permohonan/{permohonan}/submit', [PermohonanController::class, 'submit'])->name('permohonan.submit');
        });

        Route::middleware(['auth', 'role:kabkota|admin_peran'])->group(function () {
            Route::resource('permohonan-dokumen', PermohonanDokumenController::class);
        });
    
        // Untuk verifikator & pokja (nanti ditambahin)
        // Route::middleware(['role:verifikator|pokja|admin_peran'])->group(function () {
        //     Route::get('/permohonan/{permohonan}/verifikasi', [VerifikasiController::class, 'show'])->name('permohonan.verifikasi.show');
        //     Route::get('/permohonan/{permohonan}/evaluasi', [EvaluasiController::class, 'show'])->name('permohonan.evaluasi.show');
        // });
// Tambahkan di route group yang sesuai

    // Route::get('/kabupaten-kota', function() { return 'Kabupaten/Kota Management'; })->name('kabupaten-kota.index');
    // Route::get('/jenis-dokumen', function() { return 'Jenis Dokumen Management'; })->name('jenis-dokumen.index');
    // Route::resource('jenis-dokumen', JenisDokumenController::class);
    // Route::get('/tahun-anggaran', function() { return 'Tahun Anggaran Management'; })->name('tahun-anggaran.index');
    // Route::get('/tim-pokja', function() { return 'Tim Pokja Management'; })->name('tim-pokja.index');
    // Route::get('/jadwal', function() { return 'Jadwal Management'; })->name('jadwal.index');
    // Route::get('/surat-pemberitahuan', function() { return 'Surat Pemberitahuan Management'; })->name('surat-pemberitahuan.index');
    // Route::get('/permohonan', function() { return 'Permohonan Management'; })->name('permohonan.index');
    // Route::get('/permohonan-dokumen', function() { return 'Permohonan Dokumen Management'; })->name('permohonan-dokumen.index');
    Route::get('/verifikasi', function() { return 'Verifikasi Management'; })->name('verifikasi.index');
    Route::get('/evaluasi', function() { return 'Evaluasi Management'; })->name('evaluasi.index');
    Route::get('/approval', function() { return 'Approval Management'; })->name('approval.index');
    Route::get('/surat-rekomendasi', function() { return 'Surat Rekomendasi Management'; })->name('surat-rekomendasi.index');
    Route::get('/monitoring', function() { return 'Monitoring Management'; })->name('monitoring.index');
    Route::get('/kaban', function() { return 'Kaban Management'; })->name('kaban.index');
});

// Route::get('/', function () {
//     return view('index');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// require __DIR__.'/auth.php';
