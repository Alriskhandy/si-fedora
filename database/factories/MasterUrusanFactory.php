<?php

namespace Database\Factories;

use App\Models\MasterUrusan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterUrusan>
 */
class MasterUrusanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_urusan' => fake()->unique()->regexify('[1-3]\.[0-9]{2}'),
            'nama_urusan' => fake()->words(2, true),
            'status' => MasterUrusan::STATUS_AKTIF,
            'tanggal_update' => now()->toDateString(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'Tidak Aktif']);
    }
}
