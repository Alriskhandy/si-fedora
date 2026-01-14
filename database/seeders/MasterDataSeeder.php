<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kabupaten/Kota - Semua Kab/Kota di Maluku Utara
        $kabupatenKota = [
            ['kode' => '8201', 'nama' => 'Halmahera Barat', 'jenis' => 'kabupaten'],
            ['kode' => '8202', 'nama' => 'Halmahera Tengah', 'jenis' => 'kabupaten'],
            ['kode' => '8203', 'nama' => 'Halmahera Utara', 'jenis' => 'kabupaten'],
            ['kode' => '8204', 'nama' => 'Halmahera Selatan', 'jenis' => 'kabupaten'],
            ['kode' => '8205', 'nama' => 'Kepulauan Sula', 'jenis' => 'kabupaten'],
            ['kode' => '8206', 'nama' => 'Halmahera Timur', 'jenis' => 'kabupaten'],
            ['kode' => '8207', 'nama' => 'Pulau Morotai', 'jenis' => 'kabupaten'],
            ['kode' => '8208', 'nama' => 'Pulau Taliabu', 'jenis' => 'kabupaten'],
            ['kode' => '8271', 'nama' => 'Ternate', 'jenis' => 'kota'],
            ['kode' => '8272', 'nama' => 'Tidore Kepulauan', 'jenis' => 'kota'],
        ];

        foreach ($kabupatenKota as $item) {
            DB::table('kabupaten_kota')->updateOrInsert(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'jenis' => $item['jenis'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        echo "Master data seeded successfully!\n";
        echo "- Kabupaten/Kota: 10 items\n";
    }
}
