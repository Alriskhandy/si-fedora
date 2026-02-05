<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MasterJenisDokumen;

class MasterKelengkapanSeeder extends Seeder
{
    public function run(): void
    {
        // Get RKPD jenis dokumen
        $rkpd = MasterJenisDokumen::where('nama', 'RKPD')->first();
        
        if (!$rkpd) {
            throw new \Exception('Master Jenis Dokumen RKPD belum ada. Jalankan MasterJenisDokumenSeeder terlebih dahulu.');
        }

        // Sesuai dengan MasterDataSeeder - 15 dokumen kelengkapan (RKPD 2026)`
        $kelengkapan = [
            [
                'nama_dokumen' => 'Surat Permohonan Fasilitasi dari Bupati/Walikota',
                'tahapan_id' => 1, // Tahapan: Permohonan
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Surat permohonan fasilitasi Rancangan Akhir RKPD Tahun 2026 dari Bupati/Walikota kepada Gubernur Maluku Utara',
                'wajib' => true,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Surat Pengantar Kepala BAPPEDA',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Surat pengantar dari Kepala BAPPEDA Kabupaten/Kota kepada Kepala BAPPEDA Provinsi',
                'wajib' => true,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Laporan Evaluasi RKPD Semester II Tahun 2024',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Laporan evaluasi hasil pelaksanaan RKPD Semester II Tahun 2024',
                'wajib' => true,
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen Rancangan Akhir RKPD Tahun 2026',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Draft dokumen RKPD Tahun 2026',
                'wajib' => true,
                'urutan' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Berita Acara Musrenbang RKPD',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Berita acara hasil Musrenbang RKPD',
                'wajib' => true,
                'urutan' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Daftar Hadir Musrenbang RKPD',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Daftar hadir peserta Musrenbang RKPD',
                'wajib' => true,
                'urutan' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Bahan Analisis Pelengkap (Form E.35 dan E.36)',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Form hasil Pengendalian dan Evaluasi Perumusan Kebijakan Perencanaan Pembangunan',
                'wajib' => true,
                'urutan' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen Perda RPJMD / Perkada RPD',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Dokumen Perda RPJMD Kabupaten/Kota Tahun berjalan atau Perkada RPD Kab-Kota',
                'wajib' => true,
                'urutan' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Reviu APIP',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Hasil reviu dari APIP (Aparat Pengawasan Intern Pemerintah)',
                'wajib' => true,
                'urutan' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen LKPJ Bupati/Walikota Tahun 2024',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Laporan Keterangan Pertanggungjawaban (LKPJ) Bupati/Walikota Tahun 2024',
                'wajib' => true,
                'urutan' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 1: Konsistensi Tujuan Dan Sasaran',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Konsistensi Tujuan Dan Sasaran RPJMD Tahun Pelaksanaan 2026 Dan RKPD Tahun 2026',
                'wajib' => true,
                'urutan' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 2: Konsistensi Program Dan Pagu Pendanaan',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Konsistensi Program Dan Pagu Pendanaan RKPD Tahun 2026 Dan RPJMD Tahun Pelaksanaan 2025',
                'wajib' => true,
                'urutan' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 3: Daftar Indikator Kinerja',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Daftar Indikator Kinerja Penyelenggaraan Pemerintah Daerah RKPD Tahun 2026',
                'wajib' => true,
                'urutan' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 4: Keselarasan Indikator Kinerja Makro',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Daftar Keselarasan Pencapaian Indikator Kinerja Makro Pembangunan Provinsi dengan Kabupaten/Kota',
                'wajib' => true,
                'urutan' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 5: Tindak Lanjut Kebijakan Prioritas Nasional',
                'tahapan_id' => 1,
                'jenis_dokumen_id' => $rkpd->id,
                'deskripsi' => 'Daftar Tindak Lanjut Dukungan Pemerintah Daerah Atas Kebijakan Prioritas Nasional Tahun 2026',
                'wajib' => true,
                'urutan' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('master_kelengkapan_verifikasi')->insert($kelengkapan);
    }
}
