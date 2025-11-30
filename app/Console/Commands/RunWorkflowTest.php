<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\JenisDokumen;
use App\Models\TahunAnggaran;
use App\Models\JadwalFasilitasi;
use App\Models\Evaluasi;

class RunWorkflowTest extends Command
{
    protected $signature = 'test:workflow';
    protected $description = 'Jalankan testing otomatis workflow SIFEDORA';

    public function handle()
    {
        $this->info('🚀 Memulai testing workflow SIFEDORA...');

        // 1. Ambil user untuk tiap role
        $kabkota = User::whereHas('roles', function($q) {
            $q->where('name', 'kabkota');
        })->first();

        $verifikator = User::whereHas('roles', function($q) {
            $q->where('name', 'verifikator');
        })->first();

        $pokja = User::whereHas('roles', function($q) {
            $q->where('name', 'pokja');
        })->first();

        $kaban = User::whereHas('roles', function($q) {
            $q->where('name', 'kaban');
        })->first();

        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'admin_peran');
        })->first();

        if (!$kabkota || !$verifikator || !$pokja || !$kaban || !$admin) {
            $this->error('❌ User dengan role yang diperlukan tidak ditemukan!');
            return;
        }

        $this->info('✅ User ditemukan untuk semua role.');

        // 2. Ambil master data
        $jenisDokumen = JenisDokumen::first();
        $tahunAnggaran = TahunAnggaran::where('is_active', true)->first();
        $jadwal = JadwalFasilitasi::where('status', 'published')->first();

        if (!$jenisDokumen || !$tahunAnggaran || !$jadwal) {
            $this->error('❌ Master data tidak lengkap!');
            return;
        }

        // 3. Buat permohonan (Kab/Kota)
        $permohonan = Permohonan::create([
            'tahun_anggaran_id' => $tahunAnggaran->id,
            'jenis_dokumen_id' => $jenisDokumen->id,
            'jadwal_fasilitasi_id' => $jadwal->id,
            'nama_dokumen' => 'Dokumen RKPD Ternate 2025',
            'tanggal_permohonan' => now(),
            'keterangan' => 'Permohonan fasilitasi RKPD Ternate tahun 2025',
            'status' => 'draft',
            'created_by' => $kabkota->id,
            'kabupaten_kota_id' => $kabkota->kabupaten_kota_id,
        ]);

        $this->info('✅ Permohonan berhasil dibuat: ' . $permohonan->id);

        // 4. Submit permohonan
        $permohonan->update(['status' => 'submitted']);

        // 5. Assign ke Verifikator (Admin PERAN)
        $permohonan->update([
            'verifikator_id' => $verifikator->id,
        ]);

        // 6. Buat dokumen persyaratan dummy jika belum ada
        $dokumen = $permohonan->permohonanDokumen()->first();
        if (!$dokumen) {
            $persyaratan = $jenisDokumen->persyaratan()->first();
            if ($persyaratan) {
                $dokumen = PermohonanDokumen::create([
                    'permohonan_id' => $permohonan->id,
                    'persyaratan_dokumen_id' => $persyaratan->id,
                    'is_ada' => true,
                ]);
                $this->info('✅ Dokumen persyaratan dummy berhasil dibuat.');
            } else {
                $this->error('❌ Tidak ada persyaratan dokumen untuk jenis ini!');
                return;
            }
        }

        // 7. Test verifikasi dengan revision_required
        $this->info('🔄 Testing verifikasi dengan status revision_required...');
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'dokumen' => [
                $dokumen->id => [
                    'is_ada' => true,
                    'catatan' => 'Dokumen perlu direvisi sesuai panduan'
                ]
            ],
            'status_verifikasi' => 'revision_required',
            'catatan_umum' => 'Permohonan memerlukan revisi sebelum dapat dilanjutkan.'
        ]);

        try {
            $controller = app(\App\Http\Controllers\VerifikasiController::class);
            // Simulate auth
            \Illuminate\Support\Facades\Auth::login($verifikator);
            $controller->verifikasi($request, $permohonan);
            $this->info('✅ Verifikasi revision_required berhasil.');
        } catch (\Exception $e) {
            $this->error('❌ Verifikasi revision_required gagal: ' . $e->getMessage());
            return;
        }

        // 8. Test verifikasi dengan verified
        $this->info('🔄 Testing verifikasi dengan status verified...');
        $request->merge([
            'status_verifikasi' => 'verified',
            'catatan_umum' => 'Dokumen telah memenuhi semua persyaratan.'
        ]);

        try {
            $controller->verifikasi($request, $permohonan);
            $this->info('✅ Verifikasi verified berhasil.');
        } catch (\Exception $e) {
            $this->error('❌ Verifikasi verified gagal: ' . $e->getMessage());
            return;
        }

        // 7. Assign ke Tim Pokja (Admin PERAN)
        $timPokja = \App\Models\TimPokja::whereHas('anggota')->first();
if (!$timPokja) {
    $this->error('❌ Tidak ada Tim Pokja dengan anggota!');
    return;
}
        $permohonan->update([
            'pokja_id' => $timPokja->id,
            'status' => 'in_evaluation',
        ]);

        // 8. Buat draft rekomendasi (Tim Pokja)
        // Evaluasi::create([
        //     'permohonan_id' => $permohonan->id,
        //     'tahun_anggaran_id' => $tahunAnggaran->id,
        //     'draft_rekomendasi' => 'Berdasarkan hasil evaluasi, dokumen RKPD Ternate 2025 telah memenuhi persyaratan dan siap untuk direkomendasikan.',
        //     'catatan_evaluasi' => 'Perlu perhatian khusus pada indikator kinerja program prioritas.',
        //     'evaluated_by' => $pokja->id,
        //     'evaluated_at' => now(),
        // ]);
        Evaluasi::create([
            'permohonan_id' => $permohonan->id,
            'pokja_id' => $timPokja->id, // <-- Ini ID Tim Pokja
            'evaluator_id' => $pokja->id, 
            'tahun_anggaran_id' => $tahunAnggaran->id,
            'draft_rekomendasi' => 'Berdasarkan hasil evaluasi...',
            'catatan_evaluasi' => 'Perlu perhatian khusus...',
            'evaluated_by' => $pokja->id,
            'evaluated_at' => now(),
        ]);

        $permohonan->update([
            'status' => 'draft_recommendation',
        ]);

        $this->info('✅ Draft rekomendasi berhasil dibuat.');

        // 9. Approve oleh Kaban
        $permohonan->update([
            'status' => 'approved_by_kaban',
            'approved_by' => $kaban->id,
            'approved_at' => now(),
        ]);

        $this->info('✅ Draft rekomendasi berhasil disetujui oleh Kaban.');

        // 10. Generate surat rekomendasi
        $permohonan->update([
            'status' => 'letter_issued',
            'nomor_surat' => '001/REK/XII/2025',
        ]);

        $this->info('✅ Surat rekomendasi berhasil diterbitkan.');
        $this->info('🎉 Testing workflow SIFEDORA selesai! Cek dashboard semua role.');
    }
}