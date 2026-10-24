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
            'nama_urusan' => fake()->words(2, true),
            'kategori' => fake()->randomElement(['wajib_dasar', 'wajib_non_dasar', 'pilihan']),
        ];
    }
}
