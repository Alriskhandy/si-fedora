<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kabupaten/Kota - Fokus Ternate dulu
        DB::table('kabupaten_kota')->insert([
            [
                'kode' => 'KT-TRN',
                'nama' => 'Kota Ternate',
                'jenis' => 'kota',
                'provinsi' => 'Maluku Utara',
                'kepala_daerah' => 'M. Tauhid Soleman',
                'email' => 'pemkot@ternatekota.go.id',
                'telepon' => '0921-3121345',
                'alamat' => 'Jl. Pahlawan Revolusi, Ternate',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Jenis Dokumen
        $jenisDokumen = [
            ['kode' => 'RKPD', 'nama' => 'Rencana Kerja Pemerintah Daerah (RKPD)', 'deskripsi' => 'Dokumen perencanaan tahunan daerah'],
            ['kode' => 'RPJMD', 'nama' => 'Rencana Pembangunan Jangka Menengah Daerah (RPJMD)', 'deskripsi' => 'Dokumen perencanaan 5 tahunan'],
            ['kode' => 'RPD', 'nama' => 'Rencana Pembangunan Daerah (RPD)', 'deskripsi' => 'Dokumen perencanaan pembangunan daerah'],
        ];

        foreach ($jenisDokumen as $jenis) {
            DB::table('jenis_dokumen')->insert([
                'kode' => $jenis['kode'],
                'nama' => $jenis['nama'],
                'deskripsi' => $jenis['deskripsi'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Tahun Anggaran
        $tahunSekarang = date('Y');
        for ($i = 0; $i < 3; $i++) {
            DB::table('tahun_anggaran')->insert([
                'tahun' => $tahunSekarang + $i,
                'is_active' => $i === 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Persyaratan Dokumen untuk RKPD (contoh)
        $rkpdId = DB::table('jenis_dokumen')->where('kode', 'RKPD')->value('id');
        
        $persyaratan = [
            ['kode' => 'RKPD-01', 'nama' => 'Surat Permohonan Fasilitasi', 'is_wajib' => true, 'urutan' => 1],
            ['kode' => 'RKPD-02', 'nama' => 'Draft RKPD', 'is_wajib' => true, 'urutan' => 2],
            ['kode' => 'RKPD-03', 'nama' => 'Berita Acara Musrenbang', 'is_wajib' => true, 'urutan' => 3],
            ['kode' => 'RKPD-04', 'nama' => 'Dokumen RPJMD', 'is_wajib' => true, 'urutan' => 4],
            ['kode' => 'RKPD-05', 'nama' => 'Analisis Gambaran Umum Daerah', 'is_wajib' => true, 'urutan' => 5],
        ];

        foreach ($persyaratan as $item) {
            DB::table('persyaratan_dokumen')->insert([
                'jenis_dokumen_id' => $rkpdId,
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'is_wajib' => $item['is_wajib'],
                'urutan' => $item['urutan'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Tim Pokja
        DB::table('tim_pokja')->insert([
            [
                'nama' => 'Tim Pokja Perencanaan',
                'deskripsi' => 'Tim evaluasi dokumen perencanaan daerah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        echo "Master data seeded successfully!\n";
    }
}