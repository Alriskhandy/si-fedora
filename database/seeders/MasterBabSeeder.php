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
        $rpjmd = MasterJenisDokumen::where('nama', 'RPJMD')->first();
        $rpjmdPerubahan = MasterJenisDokumen::where('nama', 'RPJMD Perubahan')->first();

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
                'nama_bab' => 'BAB II GAMBARAN UMUM KONDISI DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 2,
            ],
            [
                'nama_bab' => 'BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 3,
            ],
            [
                'nama_bab' => 'BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 4,
            ],
            [
                'nama_bab' => 'BAB V RENCANA KERJA DAN PENDANAAN DAERAH',
                'jenis_dokumen_id' => $rkpd->id,
                'parent_id' => null,
                'urutan' => 5,
            ],
            [
                'nama_bab' => 'BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH',
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

        // Bab-bab untuk RPJMD Perubahan
        $babsRkpdPerubahan = [
            [
                'nama_bab' => 'BAB I PENDAHULUAN',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 1,
            ],
            [
                'nama_bab' => 'BAB II GAMBARAN UMUM KONDISI DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 2,
            ],
            [
                'nama_bab' => 'BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 3,
            ],
            [
                'nama_bab' => 'BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 4,
            ],
            [
                'nama_bab' => 'BAB V RENCANA KERJA DAN PENDANAAN DAERAH',
                'jenis_dokumen_id' => $rkpdPerubahan->id,
                'parent_id' => null,
                'urutan' => 5,
            ],
            [
                'nama_bab' => 'BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH',
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

        $babsRpjmd = [
            [
                'nama_bab' => 'BAB I PENDAHULUAN',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 1,
            ],
            [
                'nama_bab' => 'BAB II GAMBARAN UMUM KONDISI DAERAH',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 2,
            ],
            [
                'nama_bab' => 'BAB III GAMBARAN KEUANGAN DAERAH',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 3,
            ],
            [
                'nama_bab' => 'BAB IV PERMASALAHAN DAN ISU-ISU STRATEGIS DAERAH',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 4,
            ],
            [
                'nama_bab' => 'BAB V VISI, MISI, TUJUAN DAN SASARAN',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 5,
            ],
            [
                'nama_bab' => 'BAB VI STRATEGI, ARAH KEBIJAKAN DAN PROGRAM PEMBANGUNAN DAERAH',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 6,
            ],
            [
                'nama_bab' => 'BAB VII KERANGKA PENDANAAN PEMBANGUNAN DAN PROGRAM PERANGKAT DAERAH',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 7,
            ],
            [
                'nama_bab' => 'BAB VIII KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 8,
            ],
            [
                'nama_bab' => 'BAB IX PENUTUP',
                'jenis_dokumen_id' => $rpjmd->id,
                'parent_id' => null,
                'urutan' => 9,
            ],
        ];

        $babsRpjmdPerubahan = [
            [
                'nama_bab' => 'BAB I PENDAHULUAN',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 1,
            ],
            [
                'nama_bab' => 'BAB II GAMBARAN UMUM KONDISI DAERAH',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 2,
            ],
            [
                'nama_bab' => 'BAB III GAMBARAN KEUANGAN DAERAH',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 3,
            ],
            [
                'nama_bab' => 'BAB IV PERMASALAHAN DAN ISU-ISU STRATEGIS DAERAH',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 4,
            ],
            [
                'nama_bab' => 'BAB V VISI, MISI, TUJUAN DAN SASARAN',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 5,
            ],
            [
                'nama_bab' => 'BAB VI STRATEGI, ARAH KEBIJAKAN DAN PROGRAM PEMBANGUNAN DAERAH',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 6,
            ],
            [
                'nama_bab' => 'BAB VII KERANGKA PENDANAAN PEMBANGUNAN DAN PROGRAM PERANGKAT DAERAH',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 7,
            ],
            [
                'nama_bab' => 'BAB VIII KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 8,
            ],
            [
                'nama_bab' => 'BAB IX PENUTUP',
                'jenis_dokumen_id' => $rpjmdPerubahan->id,
                'parent_id' => null,
                'urutan' => 9,
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

        // Seed RPJMD babs
        foreach ($babsRpjmd as $bab) {
            MasterBab::firstOrCreate(
                [
                    'nama_bab' => $bab['nama_bab'],
                    'jenis_dokumen_id' => $bab['jenis_dokumen_id'],
                ],
                $bab
            );
        }

        // Seed RKPD Perubahan babs
        foreach ($babsRpjmdPerubahan as $bab) {
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
        $this->command->info('- RPJMD: 9 babs');
        $this->command->info('- RPJMD Perubahan: 9 babs');
    }
}
