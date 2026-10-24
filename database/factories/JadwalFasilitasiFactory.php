<?php

namespace Database\Factories;

use App\Models\JadwalFasilitasi;
use App\Models\MasterJenisDokumen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalFasilitasi>
 */
class JadwalFasilitasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tahun_anggaran' => (int) now()->format('Y'),
            'jenis_dokumen' => MasterJenisDokumen::factory(),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
            'dibuat_oleh' => User::factory(),
        ];
    }
}
