<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\JadwalFasilitasiController;
use App\Http\Controllers\SuratPemberitahuanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PermohonanDokumenController;
use App\Http\Controllers\VerifikasiController;
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
use App\Http\Controllers\UndanganPelaksanaanController;
use App\Http\Controllers\HasilFasilitasiController;
use App\Http\Controllers\ValidasiHasilController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\PenetapanPerdaController;
use App\Http\Controllers\SuratPenyampaianHasilController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TimAssignmentController;
use App\Http\Controllers\MasterBabController;
use App\Http\Controllers\MasterJenisDokumenController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/logo/index', [LogoController::class, 'index'])->name('logo.index');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =====================================================
    // SUPERADMIN ROUTES
    // =====================================================
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // =====================================================
    // MASTER DATA (Superadmin & Admin PERAN)
    // =====================================================
    Route::middleware(['role:superadmin|admin_peran'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::resource('kabupaten-kota', KabupatenKotaController::class)->parameters(['kabupaten-kota' => 'kabupatenKota']);
        Route::resource('master-tahapan', MasterTahapanController::class)->parameters(['master-tahapan' => 'masterTahapan']);
        Route::resource('master-urusan', MasterUrusanController::class)->parameters(['master-urusan' => 'masterUrusan']);
        Route::resource('master-kelengkapan', MasterKelengkapanController::class)->parameters(['master-kelengkapan' => 'masterKelengkapan']);
        Route::resource('master-jenis-dokumen', MasterJenisDokumenController::class)->parameters(['master-jenis-dokumen' => 'masterJenisDokuman']);
        Route::post('/master-jenis-dokumen/{masterJenisDokuman}/toggle-status', [MasterJenisDokumenController::class, 'toggleStatus'])->name('master-jenis-dokumen.toggle-status');
        Route::resource('master-bab', MasterBabController::class)->parameters(['master-bab' => 'masterBab']);

        // Tim Assignment Management
        Route::resource('tim-assignment', TimAssignmentController::class)->parameters(['tim-assignment' => 'timAssignment']);
        Route::post('/tim-assignment/{timAssignment}/activate', [TimAssignmentController::class, 'activate'])->name('tim-assignment.activate');
        Route::post('/tim-assignment/{timAssignment}/toggle-status', [TimAssignmentController::class, 'toggleStatus'])->name('tim-assignment.toggle-status');
        Route::post('/tim-assignment/toggle-tim-status', [TimAssignmentController::class, 'toggleTimStatus'])->name('tim-assignment.toggle-tim-status');
        Route::get('/tim-assignment/{timAssignment}/download-sk', [TimAssignmentController::class, 'downloadSk'])->name('tim-assignment.download-sk');
        Route::get('/api/tim-assignment/users', [TimAssignmentController::class, 'getAssignedUsers'])->name('tim-assignment.get-users');
    });

    // =====================================================
    // ADMIN PERAN ROUTES
    // =====================================================
    Route::middleware(['role:admin_peran'])->group(function () {
        // Jadwal Fasilitasi
        Route::resource('jadwal', JadwalFasilitasiController::class)->parameters(['jadwal' => 'jadwal']);
        Route::post('/jadwal/{jadwal}/publish', [JadwalFasilitasiController::class, 'publish'])->name('jadwal.publish');
        Route::post('/jadwal/{jadwal}/cancel', [JadwalFasilitasiController::class, 'cancel'])->name('jadwal.cancel');
        Route::get('/jadwal/{jadwal}/download', [JadwalFasilitasiController::class, 'download'])->name('jadwal.download');

        // Surat Pemberitahuan
        Route::resource('surat-pemberitahuan', SuratPemberitahuanController::class)->parameters(['surat-pemberitahuan' => 'suratPemberitahuan']);
        Route::post('/surat-pemberitahuan/{suratPemberitahuan}/send', [SuratPemberitahuanController::class, 'send'])->name('surat-pemberitahuan.send');
        Route::get('/surat-pemberitahuan/{suratPemberitahuan}/download', [SuratPemberitahuanController::class, 'download'])->name('surat-pemberitahuan.download');

        // Assignment Tim
        Route::get('/admin-peran', [AdminPeranController::class, 'index'])->name('admin-peran.index');
        Route::post('/admin-peran/{permohonan}/assign', [AdminPeranController::class, 'assign'])->name('admin-peran.assign');
        Route::post('/admin-peran/{permohonan}/unassign', [AdminPeranController::class, 'unassign'])->name('admin-peran.unassign');

        // Laporan Verifikasi
        Route::get('/laporan-verifikasi', [LaporanVerifikasiController::class, 'index'])->name('laporan-verifikasi.index');
        Route::get('/laporan-verifikasi/{permohonan}/create', [LaporanVerifikasiController::class, 'create'])->name('laporan-verifikasi.create');
        Route::post('/laporan-verifikasi/{permohonan}', [LaporanVerifikasiController::class, 'store'])->name('laporan-verifikasi.store');
        Route::get('/laporan-verifikasi/{permohonan}', [LaporanVerifikasiController::class, 'show'])->name('laporan-verifikasi.show');
        Route::get('/laporan-verifikasi/{permohonan}/download', [LaporanVerifikasiController::class, 'download'])->name('laporan-verifikasi.download');

        // Undangan Pelaksanaan
        Route::get('/undangan-pelaksanaan', [UndanganPelaksanaanController::class, 'index'])->name('undangan-pelaksanaan.index');
        Route::get('/undangan-pelaksanaan/{permohonan}/create', [UndanganPelaksanaanController::class, 'create'])->name('undangan-pelaksanaan.create');
        Route::post('/undangan-pelaksanaan/{permohonan}', [UndanganPelaksanaanController::class, 'store'])->name('undangan-pelaksanaan.store');
        Route::get('/undangan-pelaksanaan/{permohonan}', [UndanganPelaksanaanController::class, 'show'])->name('undangan-pelaksanaan.show');
        Route::post('/undangan-pelaksanaan/{permohonan}/send', [UndanganPelaksanaanController::class, 'send'])->name('undangan-pelaksanaan.send');

        // Validasi Hasil Fasilitasi
        Route::get('/validasi-hasil', [ValidasiHasilController::class, 'index'])->name('validasi-hasil.index');
        Route::get('/validasi-hasil/{permohonan}', [ValidasiHasilController::class, 'show'])->name('validasi-hasil.show');
        Route::post('/validasi-hasil/{permohonan}/approve', [ValidasiHasilController::class, 'approve'])->name('validasi-hasil.approve');
        Route::post('/validasi-hasil/{permohonan}/revise', [ValidasiHasilController::class, 'revise'])->name('validasi-hasil.revise');
        Route::get('/validasi-hasil/{permohonan}/generate', [ValidasiHasilController::class, 'generate'])->name('validasi-hasil.generate');
        Route::get('/validasi-hasil/{permohonan}/generate-pdf', [ValidasiHasilController::class, 'generatePdf'])->name('validasi-hasil.generate-pdf');
    });

    // =====================================================
    // KABAN ROUTES
    // =====================================================
    Route::middleware(['role:kaban'])->group(function () {
        // Approval
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/{permohonan}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::post('/approval/{permohonan}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{permohonan}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');

        // Penetapan Jadwal
        Route::get('/penetapan-jadwal', [PenetapanJadwalController::class, 'index'])->name('penetapan-jadwal.index');
        Route::get('/penetapan-jadwal/{permohonan}/create', [PenetapanJadwalController::class, 'create'])->name('penetapan-jadwal.create');
        Route::post('/penetapan-jadwal/{permohonan}', [PenetapanJadwalController::class, 'store'])->name('penetapan-jadwal.store');
        Route::get('/penetapan-jadwal/{permohonan}', [PenetapanJadwalController::class, 'show'])->name('penetapan-jadwal.show');

        // Surat Penyampaian Hasil
        Route::get('/surat-penyampaian-hasil', [SuratPenyampaianHasilController::class, 'index'])->name('surat-penyampaian-hasil.index');
        Route::get('/surat-penyampaian-hasil/{permohonan}/create', [SuratPenyampaianHasilController::class, 'create'])->name('surat-penyampaian-hasil.create');
        Route::post('/surat-penyampaian-hasil/{permohonan}', [SuratPenyampaianHasilController::class, 'store'])->name('surat-penyampaian-hasil.store');
        Route::get('/surat-penyampaian-hasil/{permohonan}', [SuratPenyampaianHasilController::class, 'show'])->name('surat-penyampaian-hasil.show');

        // Penetapan PERDA/PERKADA
        Route::get('/penetapan-perda', [PenetapanPerdaController::class, 'index'])->name('penetapan-perda.index');
        Route::get('/penetapan-perda/{permohonan}/create', [PenetapanPerdaController::class, 'create'])->name('penetapan-perda.create');
        Route::post('/penetapan-perda/{permohonan}', [PenetapanPerdaController::class, 'store'])->name('penetapan-perda.store');
        Route::get('/penetapan-perda/{permohonan}', [PenetapanPerdaController::class, 'show'])->name('penetapan-perda.show');
        Route::get('/penetapan-perda/{permohonan}/download', [PenetapanPerdaController::class, 'download'])->name('penetapan-perda.download');

        // Surat Rekomendasi
        Route::get('/surat-rekomendasi', [SuratRekomendasiController::class, 'index'])->name('surat-rekomendasi.index');
        Route::get('/surat-rekomendasi/{permohonan}/create', [SuratRekomendasiController::class, 'create'])->name('surat-rekomendasi.create');
        Route::post('/surat-rekomendasi/{permohonan}', [SuratRekomendasiController::class, 'store'])->name('surat-rekomendasi.store');
        Route::get('/surat-rekomendasi/{permohonan}', [SuratRekomendasiController::class, 'show'])->name('surat-rekomendasi.show');

        // Monitoring (placeholder)
        Route::get('/monitoring', function () {
            return 'Monitoring Management';
        })->name('monitoring.index');
    });

    // =====================================================
    // PEMOHON ROUTES
    // =====================================================
    Route::middleware(['role:pemohon'])->group(function () {
        // Jadwal
        Route::get('/pemohon/jadwal', [PemohonJadwalController::class, 'index'])->name('pemohon.jadwal.index');
        Route::get('/pemohon/jadwal/{jadwal}', [PemohonJadwalController::class, 'show'])->name('pemohon.jadwal.show');
        Route::get('/pemohon/jadwal/{jadwal}/download', [JadwalFasilitasiController::class, 'download'])->name('pemohon.jadwal.download');

        // Permohonan
        Route::resource('permohonan', PermohonanController::class);
        Route::post('/permohonan/{permohonan}/submit', [PermohonanController::class, 'submit'])->name('permohonan.submit');

        // Dokumen Permohonan
        Route::resource('permohonan-dokumen', PermohonanDokumenController::class)->parameters(['permohonan-dokumen' => 'permohonanDokumen']);
        Route::put('/permohonan-dokumen/{permohonanDokumen}/upload', [PermohonanDokumenController::class, 'upload'])->name('permohonan-dokumen.upload');

        // Undangan
        Route::get('/pemohon/undangan', [UndanganPelaksanaanController::class, 'myUndangan'])->name('pemohon.undangan.index');
        Route::get('/pemohon/undangan/{id}', [UndanganPelaksanaanController::class, 'view'])->name('pemohon.undangan.view');

        // Tindak Lanjut
        Route::get('/tindak-lanjut', [TindakLanjutController::class, 'index'])->name('tindak-lanjut.index');
        Route::get('/tindak-lanjut/{permohonan}/create', [TindakLanjutController::class, 'create'])->name('tindak-lanjut.create');
        Route::post('/tindak-lanjut/{permohonan}', [TindakLanjutController::class, 'store'])->name('tindak-lanjut.store');
        Route::get('/tindak-lanjut/{permohonan}', [TindakLanjutController::class, 'show'])->name('tindak-lanjut.show');
        Route::get('/tindak-lanjut/{permohonan}/download', [TindakLanjutController::class, 'download'])->name('tindak-lanjut.download');
    });

    // =====================================================
    // VERIFIKATOR ROUTES
    // =====================================================
    Route::middleware(['role:verifikator'])->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{permohonan}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{permohonan}/verifikasi', [VerifikasiController::class, 'verifikasi'])->name('verifikasi.verifikasi');
        Route::post('/verifikasi/{permohonan}/verifikasi-dokumen', [VerifikasiController::class, 'verifikasiDokumen'])->name('verifikasi.verifikasi-dokumen');
    });

    // =====================================================
    // FASILITATOR ROUTES
    // =====================================================
    Route::middleware(['role:fasilitator'])->group(function () {
        // Hasil Fasilitasi
        Route::get('/hasil-fasilitasi', [HasilFasilitasiController::class, 'index'])->name('hasil-fasilitasi.index');
        Route::get('/hasil-fasilitasi/{permohonan}/create', [HasilFasilitasiController::class, 'create'])->name('hasil-fasilitasi.create');
        Route::post('/hasil-fasilitasi/{permohonan}', [HasilFasilitasiController::class, 'store'])->name('hasil-fasilitasi.store');
        Route::get('/hasil-fasilitasi/{permohonan}', [HasilFasilitasiController::class, 'show'])->name('hasil-fasilitasi.show');
        Route::post('/hasil-fasilitasi/{permohonan}/submit', [HasilFasilitasiController::class, 'submit'])->name('hasil-fasilitasi.submit');
        Route::get('/hasil-fasilitasi/{permohonan}/download', [HasilFasilitasiController::class, 'download'])->name('hasil-fasilitasi.download');
        Route::get('/hasil-fasilitasi/{permohonan}/generate', [HasilFasilitasiController::class, 'generate'])->name('hasil-fasilitasi.generate');
        Route::get('/hasil-fasilitasi/{permohonan}/generate-pdf', [HasilFasilitasiController::class, 'generatePdf'])->name('hasil-fasilitasi.generate-pdf');

        // Sistematika & Urusan
        Route::post('/hasil-fasilitasi/{permohonan}/sistematika', [HasilFasilitasiController::class, 'storeSistematika'])->name('hasil-fasilitasi.sistematika.store');
        Route::delete('/hasil-fasilitasi/{permohonan}/sistematika/{id}', [HasilFasilitasiController::class, 'deleteSistematika'])->name('hasil-fasilitasi.sistematika.delete');
        Route::post('/hasil-fasilitasi/{permohonan}/urusan', [HasilFasilitasiController::class, 'storeUrusan'])->name('hasil-fasilitasi.urusan.store');
        Route::delete('/hasil-fasilitasi/{permohonan}/urusan/{id}', [HasilFasilitasiController::class, 'deleteUrusan'])->name('hasil-fasilitasi.urusan.delete');
    });

    // =====================================================
    // PUBLIC ROUTES (All Authenticated Users)
    // =====================================================
    // My Undangan - accessible by verifikator, fasilitator, pemohon
    Route::get('/my-undangan', [UndanganPelaksanaanController::class, 'myUndangan'])->name('my-undangan.index');
    Route::get('/my-undangan/{id}', [UndanganPelaksanaanController::class, 'view'])->name('my-undangan.view');

    Route::get('/public/surat-penyampaian-hasil', [SuratPenyampaianHasilController::class, 'publicList'])->name('public.surat-penyampaian-hasil');
    Route::get('/public/surat-penyampaian-hasil/{permohonan}/download', [SuratPenyampaianHasilController::class, 'download'])->name('public.surat-penyampaian-hasil.download');
    Route::get('/public/penetapan-perda', [PenetapanPerdaController::class, 'public'])->name('public.penetapan-perda');

    // Download undangan - accessible by all authenticated users
    Route::get('/undangan-pelaksanaan/{permohonan}/download', [UndanganPelaksanaanController::class, 'download'])->name('undangan-pelaksanaan.download');

    // =====================================================
    // PROFILE ROUTES
    // =====================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// =====================================================
// ROOT & AUTHENTICATION ROUTES
// =====================================================
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
})->name('home');

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
