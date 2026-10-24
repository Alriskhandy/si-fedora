<?php

/*
|--------------------------------------------------------------------------
| Route Fasilitasi
|--------------------------------------------------------------------------
|
| Permohonan dan seluruh tahapannya, diurutkan mengikuti alur:
|   1 Permohonan → 2 Verifikasi → (3 Penetapan jadwal: routes/web/jadwal.php)
|   → 4 Pelaksanaan → 5 Hasil fasilitasi & persetujuan Kaban → 6 Tindak lanjut → 7 Penetapan PERDA
| ditambah perpanjangan waktu.
| Controller: app/Http/Controllers/Fasilitasi.
| Dimuat di dalam grup auth + verified.phone (routes/web.php).
|
| Catatan: route di bawah hanya memeriksa permission. Hak atas permohonan tertentu
| (milik siapa, ditugaskan ke siapa) diperiksa di controller lewat PermohonanPolicy.
|
*/

use App\Http\Controllers\Fasilitasi\ApprovalController;
use App\Http\Controllers\Fasilitasi\HasilFasilitasiController;
use App\Http\Controllers\Fasilitasi\LaporanVerifikasiController;
use App\Http\Controllers\Fasilitasi\PelaksanaanFasilitasiController;
use App\Http\Controllers\Fasilitasi\PenetapanPerdaController;
use App\Http\Controllers\Fasilitasi\PermohonanController;
use App\Http\Controllers\Fasilitasi\PermohonanDokumenController;
use App\Http\Controllers\Fasilitasi\PerpanjanganWaktuController;
use App\Http\Controllers\Fasilitasi\SuratPenyampaianHasilController;
use App\Http\Controllers\Fasilitasi\TindakLanjutController;
use App\Http\Controllers\Fasilitasi\VerifikasiController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Tahapan 1 — Permohonan
// ---------------------------------------------------------------------------

Route::prefix('permohonan')->name('permohonan.')->controller(PermohonanController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    // /create harus sebelum /{permohonan}
    Route::middleware(['can:permohonan.create'])->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });

    Route::get('/{permohonan}', 'show')->name('show');

    // Halaman per tahapan (hak lihat diperiksa PermohonanPolicy)
    Route::prefix('{permohonan}/tahapan')->name('tahapan.')->group(function () {
        Route::get('/permohonan', 'tahapanPermohonan')->name('permohonan');
        Route::get('/verifikasi', 'tahapanVerifikasi')->name('verifikasi');
        Route::get('/jadwal', 'tahapanJadwal')->name('jadwal');
        Route::get('/pelaksanaan', 'tahapanPelaksanaan')->name('pelaksanaan');
        Route::get('/hasil', 'tahapanHasil')->name('hasil');
        Route::get('/tindak-lanjut', 'tahapanTindakLanjut')->name('tindak-lanjut');
        Route::get('/penetapan', 'tahapanPenetapan')->name('penetapan');

        Route::put('/update-deadline', 'updateDeadline')->name('update-deadline')->middleware('can:tahapan.update-deadline');
    });

    Route::middleware(['can:permohonan.create'])->group(function () {
        Route::delete('/{permohonan}', 'destroy')->name('destroy');
        Route::post('/{permohonan}/submit', 'submit')->name('submit');
    });
});

// File dokumen kelengkapan permohonan
Route::middleware(['can:permohonan.upload-dokumen'])->group(function () {
    Route::put('/permohonan-dokumen/{permohonanDokumen}/upload', [PermohonanDokumenController::class, 'upload'])->name('permohonan-dokumen.upload');
    Route::delete('/permohonan-dokumen/{permohonanDokumen}/file', [PermohonanDokumenController::class, 'removeFile'])->name('permohonan-dokumen.remove-file');
});

// ---------------------------------------------------------------------------
// Tahapan 2 — Verifikasi
// ---------------------------------------------------------------------------

Route::middleware(['can:permohonan.verify'])->prefix('verifikasi')->name('verifikasi.')->controller(VerifikasiController::class)->group(function () {
    Route::post('/{permohonan}/submit', 'submit')->name('submit');
    Route::get('/{permohonan}/lembar-verifikasi', 'generateLembarVerifikasi')->name('lembar-verifikasi');
});

Route::middleware(['can:laporan-verifikasi.manage'])->prefix('laporan-verifikasi')->name('laporan-verifikasi.')->controller(LaporanVerifikasiController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{permohonan}/create', 'create')->name('create');
    Route::post('/{permohonan}', 'store')->name('store');
    Route::get('/{permohonan}', 'show')->name('show');
    Route::get('/{permohonan}/download', 'download')->name('download');
});

// ---------------------------------------------------------------------------
// Tahapan 4 — Pelaksanaan
// ---------------------------------------------------------------------------

// Dokumen pelaksanaan (izin unggah/hapus diperiksa di controller: pelaksanaan.upload-dokumen)
Route::prefix('permohonan/{permohonan}/dokumen')->name('permohonan.dokumen.')->controller(PelaksanaanFasilitasiController::class)->group(function () {
    Route::post('/upload-pelaksanaan', 'uploadDokumen')->name('upload-pelaksanaan');
    Route::get('/{dokumen}/download', 'downloadDokumen')->name('download');
    Route::delete('/{dokumen}', 'deleteDokumen')->name('delete');
});

Route::prefix('pelaksanaan-fasilitasi')->name('pelaksanaan-fasilitasi.')->controller(PelaksanaanFasilitasiController::class)->group(function () {
    Route::post('/{permohonan}/complete', 'completeTahapan')->name('complete')->middleware('can:pelaksanaan.complete');
});

// ---------------------------------------------------------------------------
// Tahapan 5 — Hasil fasilitasi, validasi, dan persetujuan Kaban
// ---------------------------------------------------------------------------

// Input catatan hasil oleh Tim (catatan hanya diubah pemilik atau koordinator; dicek di controller)
Route::middleware(['can:hasil-fasilitasi.input'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
    Route::get('/{permohonan}/create', 'create')->name('create');
    Route::post('/{permohonan}', 'store')->name('store');
    Route::post('/{permohonan}/submit', 'submit')->name('submit');
    Route::get('/{permohonan}/generate', 'generate')->name('generate');
    Route::post('/{permohonan}/sistematika', 'storeSistematika')->name('sistematika.store');
    Route::put('/{permohonan}/sistematika/{id}', 'updateSistematika')->name('sistematika.update');
    Route::delete('/{permohonan}/sistematika/{id}', 'deleteSistematika')->name('sistematika.delete');
    Route::post('/{permohonan}/urusan', 'storeUrusan')->name('urusan.store');
    Route::put('/{permohonan}/urusan/{id}', 'updateUrusan')->name('urusan.update');
    Route::delete('/{permohonan}/urusan/{id}', 'deleteUrusan')->name('urusan.delete');
    Route::post('/{permohonan}/form', 'storeForm')->name('form.store');
    Route::put('/{permohonan}/form/{id}', 'updateForm')->name('form.update');
    Route::delete('/{permohonan}/form/{id}', 'deleteForm')->name('form.delete');
    Route::post('/{permohonan}/rekomendasi', 'storeRekomendasi')->name('rekomendasi.store');
    Route::put('/{permohonan}/rekomendasi/{id}', 'updateRekomendasi')->name('rekomendasi.update');
    Route::delete('/{permohonan}/rekomendasi/{id}', 'deleteRekomendasi')->name('rekomendasi.delete');
});

// Lihat & unduh hasil
Route::middleware(['can:hasil-fasilitasi.view'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{permohonan}', 'show')->name('show');
    Route::get('/{permohonan}/download-word', 'downloadWord')->name('download-word');
    Route::get('/{permohonan}/download-pdf', 'downloadPdf')->name('download-pdf');
    Route::get('/{permohonan}/preview-pdf', 'previewPdf')->name('preview-pdf');
});

// Unduh draft final yang sudah disetujui (semua pengguna login)
Route::get('/hasil-fasilitasi/{permohonan}/download-draft-final', [HasilFasilitasiController::class, 'downloadDraftFinal'])
    ->name('hasil-fasilitasi.download-draft-final');

// Unggah draft final dan ajukan ke Kaban
Route::middleware(['can:hasil-fasilitasi.submit-kaban'])->prefix('hasil-fasilitasi')->name('hasil-fasilitasi.')->controller(HasilFasilitasiController::class)->group(function () {
    Route::post('/{permohonan}/upload-draft-final', 'uploadDraftFinal')->name('upload-draft-final');
    Route::post('/{permohonan}/submit-to-kaban', 'submitToKaban')->name('submit-to-kaban');
});

// Persetujuan Kaban
Route::middleware(['can:approval.approve'])->prefix('approval')->name('approval.')->controller(ApprovalController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{permohonan}', 'show')->name('show');
    Route::get('/{permohonan}/download-draft-final', 'downloadDraftFinal')->name('download-draft-final');
    Route::post('/{permohonan}/approve', 'approve')->name('approve');
    Route::post('/{permohonan}/reject', 'reject')->name('reject');
});

// Surat penyampaian hasil
Route::middleware(['can:surat-penyampaian-hasil.manage'])->prefix('surat-penyampaian-hasil')->name('surat-penyampaian-hasil.')->controller(SuratPenyampaianHasilController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{permohonan}/create', 'create')->name('create');
    Route::post('/{permohonan}', 'store')->name('store');
    Route::get('/{permohonan}', 'show')->name('show');
});

// Daftar & unduh surat penyampaian hasil untuk semua pengguna login.
// Prefix "public" hanya nama URL; route ini tetap wajib login.
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/surat-penyampaian-hasil', [SuratPenyampaianHasilController::class, 'publicList'])->name('surat-penyampaian-hasil');
    Route::get('/surat-penyampaian-hasil/{permohonan}/download', [SuratPenyampaianHasilController::class, 'download'])->name('surat-penyampaian-hasil.download');
});

// ---------------------------------------------------------------------------
// Tahapan 6 — Tindak lanjut
// ---------------------------------------------------------------------------

Route::middleware(['can:tindak-lanjut.submit'])->prefix('tindak-lanjut')->name('tindak-lanjut.')->controller(TindakLanjutController::class)->group(function () {
    Route::post('/{permohonan}/upload', 'upload')->name('upload');
    Route::post('/{permohonan}/submit', 'submit')->name('submit');
    Route::get('/{permohonan}/download', 'download')->name('download');
});

// ---------------------------------------------------------------------------
// Tahapan 7 — Penetapan PERDA/PERKADA
// ---------------------------------------------------------------------------

Route::middleware(['can:penetapan-perda.submit'])->prefix('penetapan-perda')->name('penetapan-perda.')->controller(PenetapanPerdaController::class)->group(function () {
    Route::post('/{permohonan}/upload', 'upload')->name('upload');
    Route::post('/{permohonan}/submit', 'submit')->name('submit');
});

Route::get('/penetapan-perda/{permohonan}/download', [PenetapanPerdaController::class, 'download'])->name('penetapan-perda.download');

// ---------------------------------------------------------------------------
// Perpanjangan waktu (batas unggah dokumen atau batas pengajuan permohonan)
// Urutan grup penting: route statis (/create) sebelum /{perpanjanganWaktu}.
// ---------------------------------------------------------------------------

// Pengaju
Route::middleware(['can:perpanjangan-waktu.submit'])->prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::delete('/{perpanjanganWaktu}', 'destroy')->name('destroy');
});

// Pemroses
Route::middleware(['can:perpanjangan-waktu.process'])->prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
    Route::put('/{perpanjanganWaktu}/process', 'process')->name('process');
});

// Pengaju & pemroses (route ber-parameter terakhir)
Route::middleware(['can:perpanjangan-waktu.view'])->prefix('perpanjangan-waktu')->name('perpanjangan-waktu.')->controller(PerpanjanganWaktuController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{perpanjanganWaktu}', 'show')->name('show');
    Route::get('/{perpanjanganWaktu}/download', 'download')->name('download');
});
