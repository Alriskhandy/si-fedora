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
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\AdminPeranController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SuratRekomendasiController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\MasterTahapanController;
use App\Http\Controllers\MasterUrusanController;
use App\Http\Controllers\MasterKelengkapanController;
use App\Http\Controllers\PemohonJadwalController;
use App\Http\Controllers\LaporanVerifikasiController;
use App\Http\Controllers\PenetapanJadwalController;

// Route::middleware(['auth'])->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// });
Route::get('/logo/index', [LogoController::class, 'index'])->name('logo.index');
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
        Route::resource('master-tahapan', MasterTahapanController::class)->parameters([
            'master-tahapan' => 'masterTahapan'
        ]);
        Route::resource('master-urusan', MasterUrusanController::class)->parameters([
            'master-urusan' => 'masterUrusan'
        ]);
        Route::resource('master-kelengkapan', MasterKelengkapanController::class)->parameters([
            'master-kelengkapan' => 'masterKelengkapan'
        ]);
    });

    // Admin PERAN Management
    Route::middleware(['role:admin_peran'])->group(function () {
        Route::get('/admin-peran', [AdminPeranController::class, 'index'])->name('admin-peran.index');
        Route::post('/admin-peran/{permohonan}/assign', [AdminPeranController::class, 'assign'])->name('admin-peran.assign');
        Route::post('/admin-peran/{permohonan}/unassign', [AdminPeranController::class, 'unassign'])->name('admin-peran.unassign');
        
        // Laporan Hasil Verifikasi (Tahap 5)
        Route::get('/laporan-verifikasi', [LaporanVerifikasiController::class, 'index'])->name('laporan-verifikasi.index');
        Route::get('/laporan-verifikasi/{permohonan}/create', [LaporanVerifikasiController::class, 'create'])->name('laporan-verifikasi.create');
        Route::post('/laporan-verifikasi/{permohonan}', [LaporanVerifikasiController::class, 'store'])->name('laporan-verifikasi.store');
        Route::get('/laporan-verifikasi/{permohonan}', [LaporanVerifikasiController::class, 'show'])->name('laporan-verifikasi.show');
        Route::get('/laporan-verifikasi/{permohonan}/download', [LaporanVerifikasiController::class, 'download'])->name('laporan-verifikasi.download');
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
    Route::middleware(['role:pemohon|admin_peran'])->group(function () {
        Route::resource('permohonan', PermohonanController::class);
        Route::post('/permohonan/{permohonan}/submit', [PermohonanController::class, 'submit'])->name('permohonan.submit');
    });

    // Jadwal untuk Pemohon
    Route::middleware(['role:pemohon'])->prefix('pemohon')->name('pemohon.')->group(function () {
        Route::get('/jadwal', [PemohonJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/{jadwal}', [PemohonJadwalController::class, 'show'])->name('jadwal.show');
    });

    // Route::middleware(['auth', 'role:kabkota|admin_peran'])->group(function () {
    //     Route::resource('permohonan-dokumen', PermohonanDokumenController::class);
    // });
    Route::resource('permohonan-dokumen', PermohonanDokumenController::class)
        ->parameters([
            'permohonan-dokumen' => 'permohonanDokumen'
        ]);
    
    // Upload dokumen permohonan (AJAX)
    Route::put('/permohonan-dokumen/{permohonanDokumen}/upload', [PermohonanDokumenController::class, 'upload'])
        ->name('permohonan-dokumen.upload');

    // Verifikasi Management - verifikator only
    Route::middleware(['role:verifikator'])->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{permohonan}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{permohonan}/verifikasi', [VerifikasiController::class, 'verifikasi'])->name('verifikasi.verifikasi');
    });

    // Approval oleh Kaban
    Route::middleware(['role:kaban'])->group(function () {
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/{permohonan}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::post('/approval/{permohonan}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{permohonan}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::get('/approval/draft/{evaluasi}/download', [ApprovalController::class, 'downloadDraft'])->name('approval.download-draft');
        
        // Penetapan Jadwal Fasilitasi (Tahap 6)
        Route::get('/penetapan-jadwal', [PenetapanJadwalController::class, 'index'])->name('penetapan-jadwal.index');
        Route::get('/penetapan-jadwal/{permohonan}/create', [PenetapanJadwalController::class, 'create'])->name('penetapan-jadwal.create');
        Route::post('/penetapan-jadwal/{permohonan}', [PenetapanJadwalController::class, 'store'])->name('penetapan-jadwal.store');
        Route::get('/penetapan-jadwal/{permohonan}', [PenetapanJadwalController::class, 'show'])->name('penetapan-jadwal.show');
    });

    Route::middleware(['role:kaban'])->group(function () {
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/{permohonan}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::post('/approval/{permohonan}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{permohonan}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::get('/approval/draft/{evaluasi}/download', [ApprovalController::class, 'downloadDraft'])->name('approval.download-draft');
    });

    Route::middleware(['role:kaban'])->group(function () {
        Route::get('/surat-rekomendasi', [SuratRekomendasiController::class, 'index'])->name('surat-rekomendasi.index');
        Route::get('/surat-rekomendasi/{permohonan}/create', [SuratRekomendasiController::class, 'create'])->name('surat-rekomendasi.create');
        Route::post('/surat-rekomendasi/{permohonan}', [SuratRekomendasiController::class, 'store'])->name('surat-rekomendasi.store');

        Route::get('/surat-rekomendasi/{permohonan}', [SuratRekomendasiController::class, 'show'])
            ->name('surat-rekomendasi.show');
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
    // Route::get('/verifikasi', function() { return 'Verifikasi Management'; })->name('verifikasi.index');
    // Route::get('/evaluasi', function() { return 'Evaluasi Management'; })->name('evaluasi.index');
    // Route::get('/approval', function() { return 'Approval Management'; })->name('approval.index');
    // Route::get('/surat-rekomendasi', function () {
    //     return 'Surat Rekomendasi Management';
    // })->name('surat-rekomendasi.index');


    Route::get('/monitoring', function () {
        return 'Monitoring Management';
    })->name('monitoring.index');
    Route::get('/kaban', function () {
        return 'Kaban Management';
    })->name('kaban.index');
});

// Root route - redirect based on auth status  
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // Direct view instead of redirect for debugging
    return view('auth.login');
})->name('home');

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
