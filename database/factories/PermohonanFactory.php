<?php

namespace Database\Factories;

use App\Models\JadwalFasilitasi;
use App\Models\KabupatenKota;
use App\Models\MasterJenisDokumen;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permohonan>
 */
class PermohonanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kab_kota_id' => KabupatenKota::factory(),
            'jenis_dokumen_id' => MasterJenisDokumen::factory(),
            'jadwal_fasilitasi_id' => JadwalFasilitasi::factory(),
            'tahun' => (int) now()->format('Y'),
            'status_akhir' => 'proses',
        ];
    }
}
