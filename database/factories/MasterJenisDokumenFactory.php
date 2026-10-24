<?php

namespace Database\Factories;

use App\Models\MasterJenisDokumen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterJenisDokumen>
 */
class MasterJenisDokumenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['RPJPD', 'RPJMD', 'RKPD']),
            'status' => true,
        ];
    }
}
