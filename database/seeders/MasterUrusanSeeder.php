<?php

namespace Database\Seeders;

use App\Models\MasterUrusan;
use Illuminate\Database\Seeder;

class MasterUrusanSeeder extends Seeder
{
    use BacaCsv;

    /**
     * Master urusan dari docs/master-urusan.csv, dicocokkan lewat kode_urusan.
     *
     * Urusan baru ditambahkan; urusan yang sudah ada hanya diisi kolom yang masih kosong
     * (nama dan status yang sudah ada tidak ditimpa). Kelompok, rumpun, dan singkatan OPD pengampu
     * dihitung model dari kode; penanda tampil di hasil fasilitasi untuk urusan baru mengikuti
     * kelompok (1–3 tampil). Jalankan OpdSeeder lebih dulu agar singkatan OPD pengampu terisi.
     */
    public function run(): void
    {
        $ditambah = 0;
        $dilengkapi = 0;

        foreach ($this->bacaCsv('master-urusan.csv') as $row) {
            $data = [
                'nama_urusan' => $row['nama_urusan'],
                'kode_opd_pengampu' => $row['kode_opd_pengampu'],
                'status' => $row['status'],
                'tanggal_update' => $row['tanggal_update'],
            ];

            $urusan = MasterUrusan::where('kode_urusan', $row['kode_urusan'])->first();

            if (! $urusan) {
                MasterUrusan::create(['kode_urusan' => $row['kode_urusan']] + array_filter($data, fn ($nilai) => $nilai !== null));
                $ditambah++;
            } elseif ($this->isiKolomKosong($urusan, $data)) {
                $dilengkapi++;
            }
        }

        $this->command?->info("Urusan: {$ditambah} ditambah, {$dilengkapi} dilengkapi.");
    }
}
