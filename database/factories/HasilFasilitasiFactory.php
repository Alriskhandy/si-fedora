<?php

namespace Database\Factories;

use App\Models\HasilFasilitasi;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HasilFasilitasi>
 */
class HasilFasilitasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'permohonan_id' => Permohonan::factory(),
            'dibuat_oleh' => User::factory(),
        ];
    }
}
