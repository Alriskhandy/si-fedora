<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKelengkapanSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID jenis dokumen berdasarkan nama
        $rkpdId = DB::table('master_jenis_dokumen')->where('nama', 'RKPD')->value('id');
        
        // Kelengkapan untuk RKPD
        $kelengkapanRKPD = [
            [
                'nama_dokumen' => 'Surat Permohonan Fasilitasi dari Bupati/Walikota',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Surat permohonan fasilitasi Rancangan Akhir RKPD dari Bupati/Walikota kepada Gubernur',
                'wajib' => true,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Surat Pengantar Kepala BAPPEDA',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Surat pengantar dari Kepala BAPPEDA Kabupaten/Kota kepada Kepala BAPPEDA Provinsi',
                'wajib' => true,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Laporan Evaluasi RKPD Semester II Tahun Sebelumnya',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Laporan evaluasi hasil pelaksanaan RKPD Semester II tahun sebelumnya',
                'wajib' => true,
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen Rancangan Akhir RKPD',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Draft dokumen RKPD tahun berjalan',
                'wajib' => true,
                'urutan' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Berita Acara Musrenbang RKPD',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Berita acara hasil Musrenbang RKPD',
                'wajib' => true,
                'urutan' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Daftar Hadir Musrenbang RKPD',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Daftar hadir peserta Musrenbang RKPD',
                'wajib' => true,
                'urutan' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Bahan Analisis Pelengkap (Form E.35 dan E.36)',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Form hasil Pengendalian dan Evaluasi Perumusan Kebijakan Perencanaan Pembangunan',
                'wajib' => true,
                'urutan' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen Perda RPJMD / Perkada RPD',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Dokumen Perda RPJMD Kabupaten/Kota atau Perkada RPD',
                'wajib' => true,
                'urutan' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Reviu APIP',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Hasil reviu dari APIP (Aparat Pengawasan Intern Pemerintah)',
                'wajib' => true,
                'urutan' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'Dokumen LKPJ Bupati/Walikota Tahun Sebelumnya',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Laporan Keterangan Pertanggungjawaban (LKPJ) Bupati/Walikota tahun sebelumnya',
                'wajib' => true,
                'urutan' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 1: Konsistensi Tujuan Dan Sasaran',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Konsistensi Tujuan Dan Sasaran RPJMD Dan RKPD',
                'wajib' => true,
                'urutan' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 2: Konsistensi Program Dan Pagu Pendanaan',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Konsistensi Program Dan Pagu Pendanaan RKPD Dan RPJMD',
                'wajib' => true,
                'urutan' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 3: Daftar Indikator Kinerja',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Daftar Indikator Kinerja Penyelenggaraan Pemerintah Daerah RKPD',
                'wajib' => true,
                'urutan' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 4: Keselarasan Indikator Kinerja Makro',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Keselarasan Pencapaian Indikator Kinerja Makro Pembangunan Provinsi dengan Kabupaten/Kota',
                'wajib' => true,
                'urutan' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_dokumen' => 'FORM 5: Tindak Lanjut Kebijakan Prioritas Nasional',
                'jenis_dokumen_id' => $rkpdId,
                'deskripsi' => 'Tindak Lanjut Dukungan Pemerintah Daerah Atas Kebijakan Prioritas Nasional',
                'wajib' => true,
                'urutan' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert semua data
        DB::table('master_kelengkapan_verifikasi')->insert(array_merge(
            $kelengkapanRKPD
        ));
    }
}
