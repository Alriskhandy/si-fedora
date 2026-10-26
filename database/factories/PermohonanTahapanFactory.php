<?php

namespace Database\Factories;

use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PermohonanTahapan>
 */
class PermohonanTahapanFactory extends Factory
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
            'tahapan_id' => MasterTahapan::factory(),
            'status' => 'proses',
        ];
    }

    /**
     * Tahapan selesai pada tanggal tertentu.
     */
    public function selesai(?\DateTimeInterface $tanggal = null): static
    {
        return $this->state(fn () => ['status' => 'selesai', 'tanggal_selesai' => $tanggal ?? now()]);
    }
}
