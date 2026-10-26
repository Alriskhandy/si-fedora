<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    use BacaCsv;

    /**
     * Master OPD dari docs/master-opd.csv, dicocokkan lewat kode_opd.
     * OPD baru ditambahkan; OPD yang sudah ada hanya diisi kolom yang masih kosong.
     * Kolom id_opd di CSV diabaikan (id dibuat database).
     */
    public function run(): void
    {
        $ditambah = 0;
        $dilengkapi = 0;

        foreach ($this->bacaCsv('master-opd.csv') as $row) {
            $data = [
                'kode_wilayah' => $row['kode_wilayah'],
                'nama_opd' => $row['nama_opd'],
                'singkatan' => $row['singkatan'],
                'jenis_opd' => $row['jenis_opd'],
                'rumpun_urusan' => $row['rumpun_urusan'],
                'status' => $row['status'],
                'tanggal_update' => $row['tanggal_update'],
                'kode_urusan' => $row['kode_urusan'],
                'urusan_pemerintahan' => $row['urusan_pemerintahan'],
                'induk_opd' => $row['induk_opd'],
            ];

            $opd = Opd::where('kode_opd', $row['kode_opd'])->first();

            if (! $opd) {
                Opd::create(['kode_opd' => $row['kode_opd']] + array_filter($data, fn ($nilai) => $nilai !== null));
                $ditambah++;
            } elseif ($this->isiKolomKosong($opd, $data)) {
                $dilengkapi++;
            }
        }

        $this->command?->info("OPD: {$ditambah} ditambah, {$dilengkapi} dilengkapi.");
    }
}
