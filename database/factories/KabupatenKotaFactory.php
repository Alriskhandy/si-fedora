<?php

namespace Database\Factories;

use App\Models\KabupatenKota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KabupatenKota>
 */
class KabupatenKotaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->numerify('82##'),
            'nama' => fake()->unique()->city(),
            'jenis' => fake()->randomElement(['kabupaten', 'kota']),
            'is_active' => true,
        ];
    }
}
