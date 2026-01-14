<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterJenisDokumen;

class MasterJenisDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisDokumen = [
            [
                'nama' => 'RKPD',
                'status' => true,
            ],
            [
                'nama' => 'RKPD Perubahan',
                'status' => true,
            ],
            [
                'nama' => 'RPJMD',
                'status' => false,
            ],
            [
                'nama' => 'RPJMD Perubahan',
                'status' => false,
            ],
            [
                'nama' => 'RPJPD',
                'status' => false,
            ],
            [
                'nama' => 'RPJPD Perubahan',
                'status' => false,
            ],
        ];

        foreach ($jenisDokumen as $jd) {
            MasterJenisDokumen::firstOrCreate(
                ['nama' => $jd['nama']],
                $jd
            );
        }

        $this->command->info('Master Jenis Dokumen seeded successfully!');
    }
}
