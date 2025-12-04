<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterUrusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Berdasarkan UU No. 23 Tahun 2014 tentang Pemerintahan Daerah
     * 32 Urusan Pemerintahan yang dibagi menjadi:
     * - 6 Urusan Wajib Pelayanan Dasar
     * - 18 Urusan Wajib Non-Pelayanan Dasar
     * - 8 Urusan Pilihan
     */
    public function run(): void
    {
        // Sesuai dengan MasterDataSeeder
        $urusan = [
            // === URUSAN WAJIB PELAYANAN DASAR ===
            [
                'nama_urusan' => 'Pendidikan',
                'kategori' => 'wajib_dasar',
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Kesehatan',
                'kategori' => 'wajib_dasar',
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pekerjaan Umum dan Penataan Ruang',
                'kategori' => 'wajib_dasar',
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Perumahan Rakyat dan Kawasan Permukiman',
                'kategori' => 'wajib_dasar',
                'urutan' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Ketenteraman, Ketertiban Umum & Perlindungan Masyarakat',
                'kategori' => 'wajib_dasar',
                'urutan' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Sosial',
                'kategori' => 'wajib_dasar',
                'urutan' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === URUSAN WAJIB NON-PELAYANAN DASAR ===
            [
                'nama_urusan' => 'Tenaga Kerja',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pemberdayaan Perempuan & Perlindungan Anak',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pangan',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pertanahan',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Lingkungan Hidup',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Administrasi Kependudukan & Catatan Sipil',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pemberdayaan Masyarakat & Desa',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pengendalian Penduduk & Keluarga Berencana',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Perhubungan',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Komunikasi & Informatika',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Koperasi, UKM',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Penanaman Modal',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Kepemudaan & Olahraga',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Statistik',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Persandian',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Kebudayaan',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Perpustakaan',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Kearsipan',
                'kategori' => 'wajib_non_dasar',
                'urutan' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === URUSAN PILIHAN ===
            [
                'nama_urusan' => 'Kelautan & Perikanan',
                'kategori' => 'pilihan',
                'urutan' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pariwisata',
                'kategori' => 'pilihan',
                'urutan' => 26,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Pertanian',
                'kategori' => 'pilihan',
                'urutan' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Kehutanan',
                'kategori' => 'pilihan',
                'urutan' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Energi & Sumber Daya Mineral',
                'kategori' => 'pilihan',
                'urutan' => 29,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Perdagangan',
                'kategori' => 'pilihan',
                'urutan' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Perindustrian',
                'kategori' => 'pilihan',
                'urutan' => 31,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_urusan' => 'Transmigrasi',
                'kategori' => 'pilihan',
                'urutan' => 32,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('master_urusan')->insert($urusan);
    }
}
