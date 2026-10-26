<?php

namespace Database\Factories;

use App\Models\Opd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opd>
 */
class OpdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_opd' => '82.'.fake()->unique()->numerify('##.##'),
            'kode_wilayah' => '82',
            'nama_opd' => 'Dinas '.fake()->unique()->words(2, true),
            'singkatan' => strtoupper(fake()->lexify('????')),
            'jenis_opd' => 'Dinas',
            'rumpun_urusan' => 'Wajib Pelayanan Dasar',
            'status' => Opd::STATUS_AKTIF,
            'tanggal_update' => now()->toDateString(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'Tidak Aktif']);
    }
}
