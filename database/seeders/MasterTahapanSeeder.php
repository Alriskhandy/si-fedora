<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterTahapanSeeder extends Seeder
{
    public function run(): void
    {
        // Sesuai dengan MasterDataSeeder - 6 tahapan utama
        $tahapan = [
            [
                'nama_tahapan' => 'Permohonan',
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tahapan' => 'Verifikasi',
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tahapan' => 'Penetapan Jadwal',
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tahapan' => 'Pelaksanaan',
                'urutan' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tahapan' => 'Hasil Fasilitasi / Evaluasi',
                'urutan' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tahapan' => 'Tindak Lanjut Hasil',
                'urutan' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tahapan' => 'Penetapan PERDA / PERKADA',
                'urutan' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('master_tahapan')->insert($tahapan);
    }
}
