<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterJenisDokumen;
use App\Models\MasterBab;

class MasterBabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get RKPD jenis dokumen
        $rkpd = MasterJenisDokumen::where('nama', 'RKPD')->first();
        $rkpdPerubahan = MasterJenisDokumen::where('nama', 'RKPD Perubahan')->first();

        if (!$rkpd || !$rkpdPerubahan) {
            $this->command->error('Please run MasterJenisDokumenSeeder first!');
            return;
        }

        // Bab-bab untuk RKPD
        $babsRkpd = [
            [
                'nama_bab' => 'BAB I PENDAHULUAN',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 1,
            ],
            [
                'nama_bab' => 'BAB II EVALUASI PELAKSANAAN RKPD TAHUN LALU',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 2,
            ],
            [
                'nama_bab' => 'BAB III RANCANGAN KERANGKA EKONOMI DAERAH DAN KEBIJAKAN KEUANGAN DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 3,
            ],
            [
                'nama_bab' => 'BAB IV PRIORITAS DAN SASARAN PEMBANGUNAN DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 4,
            ],
            [
                'nama_bab' => 'BAB V RENCANA PROGRAM DAN KEGIATAN PRIORITAS DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 5,
            ],
            [
                'nama_bab' => 'BAB VI RENCANA PROGRAM PERANGKAT DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 6,
            ],
            [
                'nama_bab' => 'BAB VII PENUTUP',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 7,
            ],
        ];

        // Bab-bab untuk RKPD Perubahan
        $babsRkpdPerubahan = [
            [
                'nama_bab' => 'BAB I PENDAHULUAN',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 1,
            ],
            [
                'nama_bab' => 'BAB II EVALUASI PELAKSANAAN RKPD TAHUN BERJALAN',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 2,
            ],
            [
                'nama_bab' => 'BAB III RANCANGAN KERANGKA EKONOMI DAERAH DAN KEBIJAKAN KEUANGAN DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 3,
            ],
            [
                'nama_bab' => 'BAB IV PRIORITAS DAN SASARAN PEMBANGUNAN DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 4,
            ],
            [
                'nama_bab' => 'BAB V RENCANA PROGRAM DAN KEGIATAN PRIORITAS DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 5,
            ],
            [
                'nama_bab' => 'BAB VI RENCANA PROGRAM PERANGKAT DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 6,
            ],
            [
                'nama_bab' => 'BAB VII PENUTUP',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 7,
            ],
        ];

        // Seed RKPD babs
        foreach ($babsRkpd as $bab) {
            MasterBab::firstOrCreate(
                [
                    'nama_bab' => $bab['nama_bab'],
                    'jenis_dokumen_id' => $bab['jenis_dokumen_id'],
                ],
                $bab
            );
        }

        // Seed RKPD Perubahan babs
        foreach ($babsRkpdPerubahan as $bab) {
            MasterBab::firstOrCreate(
                [
                    'nama_bab' => $bab['nama_bab'],
                    'jenis_dokumen_id' => $bab['jenis_dokumen_id'],
                ],
                $bab
            );
        }

        $this->command->info('Master Bab seeded successfully!');
        $this->command->info('- RKPD: 7 babs');
        $this->command->info('- RKPD Perubahan: 7 babs');
    }
}
