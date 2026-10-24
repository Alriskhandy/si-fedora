<?php

namespace Database\Factories;

use App\Models\MasterTahapan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterTahapan>
 */
class MasterTahapanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_tahapan' => fake()->words(2, true),
            'urutan' => fake()->unique()->numberBetween(1, 1000),
        ];
    }
}
