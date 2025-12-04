<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKelengkapanSeeder extends Seeder
{
    public function run(): void
    {
        // Sesuai dengan MasterDataSeeder - 15 dokumen kelengkapan (RKPD 2026)
        $kelengkapan = [
            [
                'nama_dokumen' => 'Surat Permohonan Fasilitasi dari Bupati/Walikota',
                'kategori' => 'surat_permohonan',
                'tahapan_id' => 1, // Tahapan: Permohonan
                'deskripsi' => 'Surat permohonan fasilitasi Rancangan Akhir RKPD Tahun 2026 dari Bupati/Walikota kepada Gubernur Maluku Utara',
                'wajib' => true,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Surat Pengantar Kepala BAPPEDA',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Surat pengantar dari Kepala BAPPEDA Kabupaten/Kota kepada Kepala BAPPEDA Provinsi',
                'wajib' => true,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Laporan Evaluasi RKPD Semester II Tahun 2024',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Laporan evaluasi hasil pelaksanaan RKPD Semester II Tahun 2024',
                'wajib' => true,
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen Rancangan Akhir RKPD Tahun 2026',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Draft dokumen RKPD Tahun 2026',
                'wajib' => true,
                'urutan' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Berita Acara Musrenbang RKPD',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Berita acara hasil Musrenbang RKPD',
                'wajib' => true,
                'urutan' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Daftar Hadir Musrenbang RKPD',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Daftar hadir peserta Musrenbang RKPD',
                'wajib' => true,
                'urutan' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Bahan Analisis Pelengkap (Form E.35 dan E.36)',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Form hasil Pengendalian dan Evaluasi Perumusan Kebijakan Perencanaan Pembangunan',
                'wajib' => true,
                'urutan' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen Perda RPJMD / Perkada RPD',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Dokumen Perda RPJMD Kabupaten/Kota Tahun berjalan atau Perkada RPD Kab-Kota',
                'wajib' => true,
                'urutan' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Reviu APIP',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Hasil reviu dari APIP (Aparat Pengawasan Intern Pemerintah)',
                'wajib' => true,
                'urutan' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen LKPJ Bupati/Walikota Tahun 2024',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Laporan Keterangan Pertanggungjawaban (LKPJ) Bupati/Walikota Tahun 2024',
                'wajib' => true,
                'urutan' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 1: Konsistensi Tujuan Dan Sasaran',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Konsistensi Tujuan Dan Sasaran RPJMD Tahun Pelaksanaan 2026 Dan RKPD Tahun 2026',
                'wajib' => true,
                'urutan' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 2: Konsistensi Program Dan Pagu Pendanaan',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Konsistensi Program Dan Pagu Pendanaan RKPD Tahun 2026 Dan RPJMD Tahun Pelaksanaan 2025',
                'wajib' => true,
                'urutan' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 3: Daftar Indikator Kinerja',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Daftar Indikator Kinerja Penyelenggaraan Pemerintah Daerah RKPD Tahun 2026',
                'wajib' => true,
                'urutan' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 4: Keselarasan Indikator Kinerja Makro',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
                'deskripsi' => 'Daftar Keselarasan Pencapaian Indikator Kinerja Makro Pembangunan Provinsi dengan Kabupaten/Kota',
                'wajib' => true,
                'urutan' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 5: Tindak Lanjut Kebijakan Prioritas Nasional',
                'kategori' => 'kelengkapan_verifikasi',
                'tahapan_id' => 1,
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
