<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\MasterTahapanController;
use App\Http\Controllers\MasterUrusanController;
use App\Http\Controllers\MasterKelengkapanController;
use App\Http\Controllers\MasterBabController;
use App\Http\Controllers\MasterJenisDokumenController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PermohonanDokumenController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\LaporanVerifikasiController;
use App\Http\Controllers\ValidasiHasilController;
use App\Http\Controllers\JadwalFasilitasiController;
use App\Http\Controllers\PenetapanJadwalController;
use App\Http\Controllers\UndanganPelaksanaanController;
use App\Http\Controllers\HasilFasilitasiController;
use App\Http\Controllers\PelaksanaanFasilitasiController;
use App\Http\Controllers\TimAssignmentController;
use App\Http\Controllers\AdminPeranController;
use App\Http\Controllers\SuratPemberitahuanController;
use App\Http\Controllers\SuratRekomendasiController;
use App\Http\Controllers\SuratPenyampaianHasilController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\PerpanjanganWaktuController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\PenetapanPerdaController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\ArsipController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Struktur route dikelompokkan berdasarkan:
| 1. Authentication (Public)
| 2. Core System (All Authenticated Users)
| 3. Role-Based Routes (Berdasarkan hak akses)
| 4. Shared Resources (Multi-role access)
*/

// ========================================
// PUBLIC ROUTES
// ========================================
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
})->name('home');

Route::get('/logo/index', [LogoController::class, 'index'])->name('logo.index');

// ========================================
// AUTHENTICATION ROUTES
// ========================================
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// ========================================
// PROTECTED ROUTES - ALL AUTHENTICATED USERS
// ========================================
Route::middleware(['auth'])->group(function () {
    
    // ============================================================
    // CORE SYSTEM - All Authenticated Users
    // ============================================================
    
    // DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ProfileController
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // NotifikasiController
    Route::prefix('notifikasi')->name('notifikasi.')->controller(NotifikasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::delete('/', 'destroyAll')->name('destroy-all');
        Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
    });

    // ArsipController - Archive of all documents
    Route::prefix('arsip')->name('arsip.')->controller(ArsipController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}', 'show')->name('show');
    });

    // ============================================================
    // SYSTEM ADMINISTRATION
    // ============================================================
    
    // RoleController & PermissionController (Superadmin only)
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // ActivityLogController (Superadmin, Admin, Kaban, Auditor)
    Route::middleware(['role:superadmin|admin_peran|kaban|auditor'])->prefix('activity-log')->name('activity-log.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    });

    // UserController (Superadmin & Admin)
    Route::middleware(['role:superadmin|admin_peran'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // ============================================================
    // MASTER DATA MANAGEMENT
    // ============================================================
    
    Route::middleware(['role:superadmin|admin_peran'])->group(function () {
        
        // KabupatenKotaController
        Route::resource('kabupaten-kota', KabupatenKotaController::class)->parameters(['kabupaten-kota' => 'kabupatenKota']);

        // MasterTahapanController
        Route::resource('master-tahapan', MasterTahapanController::class)->parameters(['master-tahapan' => 'masterTahapan']);

        // MasterUrusanController
        Route::resource('master-urusan', MasterUrusanController::class)->parameters(['master-urusan' => 'masterUrusan']);

        // MasterKelengkapanController
        Route::resource('master-kelengkapan', MasterKelengkapanController::class)->parameters(['master-kelengkapan' => 'masterKelengkapan']);

        // MasterJenisDokumenController
        Route::resource('master-jenis-dokumen', MasterJenisDokumenController::class)->parameters(['master-jenis-dokumen' => 'masterJenisDokuman']);
        Route::post('/master-jenis-dokumen/{masterJenisDokuman}/toggle-status', [MasterJenisDokumenController::class, 'toggleStatus'])->name('master-jenis-dokumen.toggle-status');

        // MasterBabController
        Route::resource('master-bab', MasterBabController::class)->parameters(['master-bab' => 'masterBab']);
    });

    // ============================================================
    // JADWAL & PENJADWALAN
    // ============================================================
    
    // JadwalFasilitasiController (All can view, Admin can manage)
    Route::prefix('jadwal')->name('jadwal.')->controller(JadwalFasilitasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        
        Route::middleware(['role:admin_peran'])->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{jadwal}/edit', 'edit')->name('edit');
            Route::put('/{jadwal}', 'update')->name('update');
            Route::delete('/{jadwal}', 'destroy')->name('destroy');
        });
        
        Route::get('/{jadwal}', 'show')->name('show');
        Route::get('/{jadwal}/download', 'download')->name('download');
    });

    // PenetapanJadwalController (Kaban only)
    Route::middleware(['role:kaban'])->prefix('penetapan-jadwal')->name('penetapan-jadwal.')->controller(PenetapanJadwalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::get('/{permohonan}', 'show')->name('show');
    });

    // ============================================================
    // PERMOHONAN MANAGEMENT
    // ============================================================
    
    // PermohonanController (All authenticated users can view, Pemohon can manage)
    Route::prefix('permohonan')->name('permohonan.')->controller(PermohonanController::class)->group(function () {
        // View routes - accessible by all authenticated users
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}', 'show')->name('show');
        Route::get('/{permohonan}/tab', 'showWithTabs')->name('show-tabs');
        
        // Tahapan routes - accessible by all authenticated users
        Route::prefix('{permohonan}/tahapan')->name('tahapan.')->group(function () {
            Route::get('/permohonan', 'tahapanPermohonan')->name('permohonan');
            Route::get('/verifikasi', 'tahapanVerifikasi')->name('verifikasi');
            Route::get('/jadwal', 'tahapanJadwal')->name('jadwal');
            Route::get('/pelaksanaan', 'tahapanPelaksanaan')->name('pelaksanaan');
            Route::get('/hasil', 'tahapanHasil')->name('hasil');
            Route::get('/tindak-lanjut', 'tahapanTindakLanjut')->name('tindak-lanjut');
            Route::get('/penetapan', 'tahapanPenetapan')->name('penetapan');
            
            // Update deadline - only admin & superadmin
            Route::put('/update-deadline', 'updateDeadline')->name('update-deadline')->middleware('role:admin_peran|superadmin');
        });
        
        // Management routes - only pemohon
        Route::middleware(['role:pemohon'])->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{permohonan}/edit', 'edit')->name('edit');
            Route::put('/{permohonan}', 'update')->name('update');
            Route::delete('/{permohonan}', 'destroy')->name('destroy');
            Route::post('/{permohonan}/submit', 'submit')->name('submit');
        });
    });

    // PermohonanDokumenController (Pemohon only)
    Route::middleware(['role:pemohon'])->group(function () {
        Route::resource('permohonan-dokumen', PermohonanDokumenController::class)->parameters(['permohonan-dokumen' => 'permohonanDokumen']);
        Route::put('/permohonan-dokumen/{permohonanDokumen}/upload', [PermohonanDokumenController::class, 'upload'])->name('permohonan-dokumen.upload');
    });

    // Dokumen Tahapan Routes (for upload documents at different stages)
    Route::prefix('permohonan/{permohonan}/dokumen')->name('permohonan.dokumen.')->controller(PelaksanaanFasilitasiController::class)->group(function () {
        Route::post('/upload-pelaksanaan', 'uploadDokumen')->name('upload-pelaksanaan');
        Route::get('/{dokumen}/download', 'downloadDokumen')->name('download');
        Route::delete('/{dokumen}', 'deleteDokumen')->name('delete');
    });

    // Pelaksanaan Fasilitasi Routes
    Route::prefix('pelaksanaan-fasilitasi')->name('pelaksanaan-fasilitasi.')->controller(PelaksanaanFasilitasiController::class)->group(function () {
        Route::post('/{permohonan}/complete', 'completeTahapan')->name('complete')->middleware('role:admin_peran|superadmin');
    });

    // AdminPeranController (Admin only - Assignment Tim)
    Route::middleware(['role:admin_peran'])->prefix('admin-peran')->name('admin-peran.')->controller(AdminPeranController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{permohonan}/assign', 'assign')->name('assign');
        Route::post('/{permohonan}/unassign', 'unassign')->name('unassign');
    });

    // ============================================================
    // VERIFIKASI & VALIDASI
    // ============================================================
    
    // VerifikasiController (Verifikator only)
    Route::middleware(['role:verifikator'])->prefix('verifikasi')->name('verifikasi.')->controller(VerifikasiController::class)->group(function () {
        Route::post('/{permohonan}/verifikasi', 'verifikasi')->name('verifikasi');
        Route::post('/{permohonan}/verifikasi-dokumen', 'verifikasiDokumen')->name('verifikasi-dokumen');
        Route::post('/{permohonan}/submit', 'submit')->name('submit');
    });

    // LaporanVerifikasiController (Admin only)
    Route::middleware(['role:admin_peran'])->prefix('laporan-verifikasi')->name('laporan-verifikasi.')->controller(LaporanVerifikasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::get('/{permohonan}', 'show')->name('show');
        Route::get('/{permohonan}/download', 'download')->name('download');
    });

    // ValidasiHasilController (Admin only)
    Route::middleware(['role:admin_peran'])->prefix('validasi-hasil')->name('validasi-hasil.')->controller(ValidasiHasilController::class)->group(function () {
        Route::post('/{permohonan}/approve', 'approve')->name('approve');
        Route::post('/{permohonan}/revise', 'revise')->name('revise');
        Route::get('/{permohonan}/generate', 'generate')->name('generate');
        Route::get('/{permohonan}/generate-pdf', 'generatePdf')->name('generate-pdf');
    });

    // ApprovalController (Kaban only)
    Route::middleware(['role:kaban'])->prefix('approval')->name('approval.')->controller(ApprovalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}', 'show')->name('show');
        Route::get('/{permohonan}/download-draft-final', 'downloadDraftFinal')->name('download-draft-final');
        Route::post('/{permohonan}/approve', 'approve')->name('approve');
        Route::post('/{permohonan}/reject', 'reject')->name('reject');
    });

    // ============================================================
    // FASILITASI & HASIL
    // ============================================================
    
    // HasilFasilitasiController (Fasilitator: Create/Edit, Verifikator: Read, Admin: Read All)
    Route::middleware(['role:fasilitator'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::post('/{permohonan}/submit', 'submit')->name('submit');
        Route::get('/{permohonan}/generate', 'generate')->name('generate'); // Koordinator only: create draft
        Route::post('/{permohonan}/sistematika', 'storeSistematika')->name('sistematika.store');
        Route::delete('/{permohonan}/sistematika/{id}', 'deleteSistematika')->name('sistematika.delete');
        Route::post('/{permohonan}/urusan', 'storeUrusan')->name('urusan.store');
        Route::delete('/{permohonan}/urusan/{id}', 'deleteUrusan')->name('urusan.delete');
    });
    
    Route::middleware(['role:fasilitator|verifikator|admin_peran|superadmin|kaban'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}', 'show')->name('show');
        Route::get('/{permohonan}/download', 'download')->name('download');
        Route::get('/{permohonan}/download-word', 'downloadWord')->name('download-word'); // Download Word draft
        Route::get('/{permohonan}/download-pdf', 'downloadPdf')->name('download-pdf'); // Download PDF
        Route::get('/{permohonan}/preview-pdf', 'previewPdf')->name('preview-pdf'); // Preview PDF in browser
    });

    // Public download route for approved draft final (all authenticated users)
    Route::middleware(['auth'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
        Route::get('/{permohonan}/download-draft-final', 'downloadDraftFinal')->name('download-draft-final'); // Download PDF final (accessible by all authenticated users)
    });

    // Admin & Koordinator routes untuk draft final dan submit ke kaban
    Route::middleware(['role:admin_peran|superadmin|fasilitator'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
        Route::post('/{permohonan}/upload-draft-final', 'uploadDraftFinal')->name('upload-draft-final'); // Upload draft final PDF (Admin/Koordinator)
        Route::post('/{permohonan}/submit-to-kaban', 'submitToKaban')->name('submit-to-kaban'); // Submit ke Kepala Badan (Admin only, checked in controller)
    });

    // ============================================================
    // TIM & UNDANGAN
    // ============================================================
    
    // TimAssignmentController (Superadmin & Admin)
    Route::middleware(['role:superadmin|admin_peran'])->prefix('tim-assignment')->name('tim-assignment.')->controller(TimAssignmentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{timAssignment}', 'show')->name('show');
        Route::get('/{timAssignment}/edit', 'edit')->name('edit');
        Route::put('/{timAssignment}', 'update')->name('update');
        Route::delete('/{timAssignment}', 'destroy')->name('destroy');
        Route::post('/{timAssignment}/activate', 'activate')->name('activate');
        Route::post('/{timAssignment}/toggle-status', 'toggleStatus')->name('toggle-status');
        Route::post('/toggle-tim-status', 'toggleTimStatus')->name('toggle-tim-status');
        Route::get('/{timAssignment}/download-sk', 'downloadSk')->name('download-sk');
        Route::get('/api/tim-assignment/users', 'getAssignedUsers')->name('get-users');
    });

    // UndanganPelaksanaanController (Admin: Manage, All: View)
    Route::prefix('my-undangan')->name('my-undangan.')->controller(UndanganPelaksanaanController::class)->group(function () {
        Route::get('/', 'myUndangan')->name('index');
        Route::get('/{id}', 'view')->name('view');
    });
    
    Route::middleware(['role:admin_peran'])->prefix('undangan-pelaksanaan')->name('undangan-pelaksanaan.')->controller(UndanganPelaksanaanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::get('/{permohonan}', 'show')->name('show');
        Route::post('/{permohonan}/send', 'send')->name('send');
    });
    
    Route::middleware(['role:pemohon'])->prefix('pemohon/undangan')->name('pemohon.undangan.')->controller(UndanganPelaksanaanController::class)->group(function () {
        Route::get('/', 'myUndangan')->name('index');
        Route::get('/{id}', 'view')->name('view');
    });

    Route::get('/undangan-pelaksanaan/{permohonan}/download', [UndanganPelaksanaanController::class, 'download'])->name('undangan-pelaksanaan.download');

    // ============================================================
    // SURAT-SURAT
    // ============================================================
    
    // SuratPemberitahuanController (Admin only)
    Route::middleware(['role:admin_peran'])->prefix('surat-pemberitahuan')->name('surat-pemberitahuan.')->controller(SuratPemberitahuanController::class)->group(function () {
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

    // SuratRekomendasiController (Kaban only)
    Route::middleware(['role:kaban'])->prefix('surat-rekomendasi')->name('surat-rekomendasi.')->controller(SuratRekomendasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::get('/{permohonan}', 'show')->name('show');
    });

    // SuratPenyampaianHasilController (Kaban: Manage, All: View)
    Route::middleware(['role:kaban'])->prefix('surat-penyampaian-hasil')->name('surat-penyampaian-hasil.')->controller(SuratPenyampaianHasilController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::get('/{permohonan}', 'show')->name('show');
    });
    
    Route::prefix('public')->name('public.')->group(function () {
        Route::get('/surat-penyampaian-hasil', [SuratPenyampaianHasilController::class, 'publicList'])->name('surat-penyampaian-hasil');
        Route::get('/surat-penyampaian-hasil/{permohonan}/download', [SuratPenyampaianHasilController::class, 'download'])->name('surat-penyampaian-hasil.download');
    });

    // ============================================================
    // PERPANJANGAN & TINDAK LANJUT
    // ============================================================
    
    // PerpanjanganWaktuController (Pemohon: Create, Admin: Process)
    Route::middleware(['role:pemohon'])->prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::delete('/{perpanjanganWaktu}', 'destroy')->name('destroy');
        Route::put('/{perpanjanganWaktu}/upload-surat', 'uploadSurat')->name('upload-surat');
    });
    
    Route::middleware(['role:admin_peran|superadmin'])->prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{perpanjanganWaktu}', 'show')->name('show');
        Route::get('/{perpanjanganWaktu}/download', 'download')->name('download');
        Route::post('/', 'store')->name('store'); // Admin bisa perpanjang waktu dari halaman hasil
        Route::put('/{perpanjanganWaktu}/process', 'process')->name('process');
    });

    // TindakLanjutController (Pemohon only)
    Route::middleware(['role:pemohon'])->prefix('tindak-lanjut')->name('tindak-lanjut.')->controller(TindakLanjutController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::post('/{permohonan}/upload', 'upload')->name('upload');
        Route::post('/{permohonan}/submit', 'submit')->name('submit');
        Route::get('/{permohonan}/download', 'download')->name('download');
    });

    // ============================================================
    // PENETAPAN PERDA
    // ============================================================
    
    // PenetapanPerdaController (Pemohon: Manage, All: View)
    Route::middleware(['role:pemohon'])->prefix('penetapan-perda')->name('penetapan-perda.')->controller(PenetapanPerdaController::class)->group(function () {
        Route::post('/{permohonan}/upload', 'upload')->name('upload');
        Route::post('/{permohonan}/submit', 'submit')->name('submit');
    });
    
    Route::get('/penetapan-perda/{permohonan}/download', [PenetapanPerdaController::class, 'download'])->name('penetapan-perda.download');
});
