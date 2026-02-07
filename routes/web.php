<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotifikasiController;
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
use App\Http\Controllers\PemohonJadwalController;
use App\Http\Controllers\PenetapanJadwalController;
use App\Http\Controllers\UndanganPelaksanaanController;
use App\Http\Controllers\HasilFasilitasiController;
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
    
    // ------------------------------------------------------------
    // CORE SYSTEM - Dashboard & Profile
    // ------------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ------------------------------------------------------------
    // NOTIFIKASI - Accessible by all authenticated users
    // ------------------------------------------------------------
    Route::prefix('notifikasi')->name('notifikasi.')->controller(NotifikasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::delete('/', 'destroyAll')->name('destroy-all');
        Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
    });

    // ------------------------------------------------------------
    // PUBLIC ACCESS - All authenticated users
    // ------------------------------------------------------------
    Route::prefix('my-undangan')->name('my-undangan.')->controller(UndanganPelaksanaanController::class)->group(function () {
        Route::get('/', 'myUndangan')->name('index');
        Route::get('/{id}', 'view')->name('view');
    });

    Route::prefix('public')->name('public.')->group(function () {
        Route::get('/surat-penyampaian-hasil', [SuratPenyampaianHasilController::class, 'publicList'])->name('surat-penyampaian-hasil');
        Route::get('/surat-penyampaian-hasil/{permohonan}/download', [SuratPenyampaianHasilController::class, 'download'])->name('surat-penyampaian-hasil.download');
        Route::get('/penetapan-perda', [PenetapanPerdaController::class, 'public'])->name('penetapan-perda');
    });

    Route::get('/penetapan-perda/{permohonan}/download', [PenetapanPerdaController::class, 'download'])->name('penetapan-perda.download');
    Route::get('/undangan-pelaksanaan/{permohonan}/download', [UndanganPelaksanaanController::class, 'download'])->name('undangan-pelaksanaan.download');

    // ============================================================
    // ROLE-BASED ROUTES
    // ============================================================

    // ------------------------------------------------------------
    // SUPERADMIN ROUTES - Full System Access
    // ------------------------------------------------------------
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // ------------------------------------------------------------
    // SUPERADMIN & ADMIN PERAN - Master Data & User Management
    // ------------------------------------------------------------
    Route::middleware(['role:superadmin|admin_peran'])->group(function () {
        
        // User Management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Master Data - Kabupaten/Kota
        Route::resource('kabupaten-kota', KabupatenKotaController::class)->parameters(['kabupaten-kota' => 'kabupatenKota']);

        // Master Data - Tahapan
        Route::resource('master-tahapan', MasterTahapanController::class)->parameters(['master-tahapan' => 'masterTahapan']);

        // Master Data - Urusan
        Route::resource('master-urusan', MasterUrusanController::class)->parameters(['master-urusan' => 'masterUrusan']);

        // Master Data - Kelengkapan Verifikasi
        Route::resource('master-kelengkapan', MasterKelengkapanController::class)->parameters(['master-kelengkapan' => 'masterKelengkapan']);

        // Master Data - Jenis Dokumen
        Route::resource('master-jenis-dokumen', MasterJenisDokumenController::class)->parameters(['master-jenis-dokumen' => 'masterJenisDokuman']);
        Route::post('/master-jenis-dokumen/{masterJenisDokuman}/toggle-status', [MasterJenisDokumenController::class, 'toggleStatus'])->name('master-jenis-dokumen.toggle-status');

        // Master Data - Bab (Sistematika)
        Route::resource('master-bab', MasterBabController::class)->parameters(['master-bab' => 'masterBab']);

        // Tim Assignment Management
        Route::prefix('tim-assignment')->name('tim-assignment.')->controller(TimAssignmentController::class)->group(function () {
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
        });

        Route::get('/api/tim-assignment/users', [TimAssignmentController::class, 'getAssignedUsers'])->name('tim-assignment.get-users');
    });

    // ------------------------------------------------------------
    // ADMIN PERAN ROUTES - Penjadwalan, Verifikasi, Validasi
    // ------------------------------------------------------------
    Route::middleware(['role:admin_peran'])->group(function () {
        
        // Jadwal Fasilitasi
        Route::prefix('jadwal')->name('jadwal.')->controller(JadwalFasilitasiController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{jadwal}', 'show')->name('show');
            Route::get('/{jadwal}/edit', 'edit')->name('edit');
            Route::put('/{jadwal}', 'update')->name('update');
            Route::delete('/{jadwal}', 'destroy')->name('destroy');
            Route::post('/{jadwal}/publish', 'publish')->name('publish');
            Route::post('/{jadwal}/cancel', 'cancel')->name('cancel');
            Route::get('/{jadwal}/download', 'download')->name('download');
        });

        // Surat Pemberitahuan
        Route::prefix('surat-pemberitahuan')->name('surat-pemberitahuan.')->controller(SuratPemberitahuanController::class)->group(function () {
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

        // Assignment Tim
        Route::prefix('admin-peran')->name('admin-peran.')->controller(AdminPeranController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{permohonan}/assign', 'assign')->name('assign');
            Route::post('/{permohonan}/unassign', 'unassign')->name('unassign');
        });

        // Laporan Verifikasi
        Route::prefix('laporan-verifikasi')->name('laporan-verifikasi.')->controller(LaporanVerifikasiController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
            Route::get('/{permohonan}/download', 'download')->name('download');
        });

        // Undangan Pelaksanaan
        Route::prefix('undangan-pelaksanaan')->name('undangan-pelaksanaan.')->controller(UndanganPelaksanaanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
            Route::post('/{permohonan}/send', 'send')->name('send');
        });

        // Validasi Hasil Fasilitasi
        Route::prefix('validasi-hasil')->name('validasi-hasil.')->controller(ValidasiHasilController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}', 'show')->name('show');
            Route::post('/{permohonan}/approve', 'approve')->name('approve');
            Route::post('/{permohonan}/revise', 'revise')->name('revise');
            Route::get('/{permohonan}/generate', 'generate')->name('generate');
            Route::get('/{permohonan}/generate-pdf', 'generatePdf')->name('generate-pdf');
        });

        // Perpanjangan Waktu (Admin can view and process)
        Route::prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{perpanjanganWaktu}', 'show')->name('show');
            Route::get('/{perpanjanganWaktu}/download', 'download')->name('download');
            Route::put('/{perpanjanganWaktu}/process', 'process')->name('process');
        });
    });

    // ------------------------------------------------------------
    // KABAN ROUTES - Approval & Penetapan
    // ------------------------------------------------------------
    Route::middleware(['role:kaban'])->group(function () {
        
        // Approval Draft Fasilitasi
        Route::prefix('approval')->name('approval.')->controller(ApprovalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}', 'show')->name('show');
            Route::post('/{permohonan}/approve', 'approve')->name('approve');
            Route::post('/{permohonan}/reject', 'reject')->name('reject');
        });

        // Penetapan Jadwal
        Route::prefix('penetapan-jadwal')->name('penetapan-jadwal.')->controller(PenetapanJadwalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
        });

        // Surat Penyampaian Hasil
        Route::prefix('surat-penyampaian-hasil')->name('surat-penyampaian-hasil.')->controller(SuratPenyampaianHasilController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
        });

        // Surat Rekomendasi
        Route::prefix('surat-rekomendasi')->name('surat-rekomendasi.')->controller(SuratRekomendasiController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
        });

        // Monitoring (placeholder)
        Route::get('/monitoring', function () {
            return 'Monitoring Management';
        })->name('monitoring.index');
    });

    // ------------------------------------------------------------
    // PEMOHON ROUTES - Permohonan & Dokumen
    // ------------------------------------------------------------
    Route::middleware(['role:pemohon'])->group(function () {
        
        // Jadwal (View only for Pemohon)
        Route::prefix('pemohon/jadwal')->name('pemohon.jadwal.')->group(function () {
            Route::get('/', [PemohonJadwalController::class, 'index'])->name('index');
            Route::get('/{jadwal}', [PemohonJadwalController::class, 'show'])->name('show');
            Route::get('/{jadwal}/download', [JadwalFasilitasiController::class, 'download'])->name('download');
        });

        // Permohonan (Create, Edit, Delete - Pemohon only)
        Route::prefix('permohonan')->name('permohonan.')->controller(PermohonanController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{permohonan}/edit', 'edit')->name('edit');
            Route::put('/{permohonan}', 'update')->name('update');
            Route::delete('/{permohonan}', 'destroy')->name('destroy');
            Route::post('/{permohonan}/submit', 'submit')->name('submit');
            Route::get('/{permohonan}/tab', 'showWithTabs')->name('show-tabs');
        });

        // Dokumen Permohonan
        Route::resource('permohonan-dokumen', PermohonanDokumenController::class)->parameters(['permohonan-dokumen' => 'permohonanDokumen']);
        Route::put('/permohonan-dokumen/{permohonanDokumen}/upload', [PermohonanDokumenController::class, 'upload'])->name('permohonan-dokumen.upload');

        // Perpanjangan Waktu (Pemohon can create and delete their own)
        Route::prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{perpanjanganWaktu}', 'destroy')->name('destroy');
            Route::put('/{perpanjanganWaktu}/upload-surat', 'uploadSurat')->name('upload-surat');
        });

        // Undangan (Pemohon view)
        Route::prefix('pemohon/undangan')->name('pemohon.undangan.')->controller(UndanganPelaksanaanController::class)->group(function () {
            Route::get('/', 'myUndangan')->name('index');
            Route::get('/{id}', 'view')->name('view');
        });

        // Tindak Lanjut
        Route::prefix('tindak-lanjut')->name('tindak-lanjut.')->controller(TindakLanjutController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
            Route::get('/{permohonan}/download', 'download')->name('download');
        });

        // Penetapan PERDA/PERKADA
        Route::prefix('penetapan-perda')->name('penetapan-perda.')->controller(PenetapanPerdaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{permohonan}/create', 'create')->name('create');
            Route::post('/{permohonan}', 'store')->name('store');
            Route::get('/{permohonan}', 'show')->name('show');
        });
    });

    // ------------------------------------------------------------
    // VERIFIKATOR ROUTES - Verifikasi Dokumen
    // ------------------------------------------------------------
    Route::middleware(['role:verifikator'])->prefix('verifikasi')->name('verifikasi.')->controller(VerifikasiController::class)->group(function () {
        Route::post('/{permohonan}/verifikasi', 'verifikasi')->name('verifikasi');
        Route::post('/{permohonan}/verifikasi-dokumen', 'verifikasiDokumen')->name('verifikasi-dokumen');
    });

    // ------------------------------------------------------------
    // FASILITATOR ROUTES - Hasil Fasilitasi (Create/Edit)
    // ------------------------------------------------------------
    Route::middleware(['role:fasilitator'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
        Route::get('/{permohonan}/create', 'create')->name('create');
        Route::post('/{permohonan}', 'store')->name('store');
        Route::post('/{permohonan}/submit', 'submit')->name('submit');
        Route::get('/{permohonan}/generate', 'generate')->name('generate');
        Route::get('/{permohonan}/generate-pdf', 'generatePdf')->name('generate-pdf');
        
        // Sistematika & Urusan
        Route::post('/{permohonan}/sistematika', 'storeSistematika')->name('sistematika.store');
        Route::delete('/{permohonan}/sistematika/{id}', 'deleteSistematika')->name('sistematika.delete');
        Route::post('/{permohonan}/urusan', 'storeUrusan')->name('urusan.store');
        Route::delete('/{permohonan}/urusan/{id}', 'deleteUrusan')->name('urusan.delete');
    });

    // ============================================================
    // SHARED ACCESS ROUTES - Multiple Roles
    // ============================================================

    // ------------------------------------------------------------
    // PEMOHON & VERIFIKATOR - Permohonan (Read Access)
    // ------------------------------------------------------------
    Route::middleware(['role:pemohon|verifikator'])->prefix('permohonan')->name('permohonan.')->controller(PermohonanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}', 'show')->name('show');
    });

    // ------------------------------------------------------------
    // FASILITATOR & VERIFIKATOR - Hasil Fasilitasi (Read Access)
    // ------------------------------------------------------------
    Route::middleware(['role:fasilitator|verifikator'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{permohonan}', 'show')->name('show');
        Route::get('/{permohonan}/download', 'download')->name('download');
    });
});
