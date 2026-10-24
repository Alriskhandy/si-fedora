<?php

namespace Tests\Feature;

use App\Models\JadwalFasilitasi;
use App\Models\Permohonan;
use App\Models\PerpanjanganWaktu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermohonanDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_deadline_falls_back_to_schedule_request_deadline(): void
    {
        $this->freezeSecond();
        $jadwal = JadwalFasilitasi::factory()->create(['batas_permohonan' => now()->addDays(5)]);
        $permohonan = Permohonan::factory()->for($jadwal, 'jadwalFasilitasi')->create();

        $this->assertTrue($permohonan->getEffectiveDeadline()->equalTo(now()->addDays(5)));
        $this->assertFalse($permohonan->isUploadDeadlinePassed());
    }

    public function test_processed_extension_overrides_schedule_deadline(): void
    {
        $this->freezeSecond();
        $jadwal = JadwalFasilitasi::factory()->create(['batas_permohonan' => now()->subDay()]);
        $permohonan = Permohonan::factory()->for($jadwal, 'jadwalFasilitasi')->create();
        $this->createPerpanjangan(['permohonan_id' => $permohonan->id, 'user_id' => $permohonan->user_id, 'batas_waktu' => now()->addDays(7), 'diproses_at' => now()]);

        $this->assertTrue($permohonan->getEffectiveDeadline()->equalTo(now()->addDays(7)));
        $this->assertFalse($permohonan->isUploadDeadlinePassed());
    }

    public function test_unprocessed_extension_is_ignored(): void
    {
        $this->freezeSecond();
        $jadwal = JadwalFasilitasi::factory()->create(['batas_permohonan' => now()->subDay()]);
        $permohonan = Permohonan::factory()->for($jadwal, 'jadwalFasilitasi')->create();
        $this->createPerpanjangan(['permohonan_id' => $permohonan->id, 'user_id' => $permohonan->user_id, 'batas_waktu' => now()->addDays(7)]);

        $this->assertTrue($permohonan->isUploadDeadlinePassed());
    }

    public function test_schedule_based_extension_of_the_same_applicant_applies_to_document_upload(): void
    {
        $this->freezeSecond();
        $jadwal = JadwalFasilitasi::factory()->create(['batas_permohonan' => now()->subDay()]);
        $permohonan = Permohonan::factory()->for($jadwal, 'jadwalFasilitasi')->create();
        $this->createPerpanjangan(['jadwal_fasilitasi_id' => $jadwal->id, 'user_id' => $permohonan->user_id, 'batas_waktu' => now()->addDays(3), 'diproses_at' => now()]);

        $this->assertTrue($permohonan->getEffectiveDeadline()->equalTo(now()->addDays(3)));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPerpanjangan(array $attributes): void
    {
        PerpanjanganWaktu::create($attributes + [
            'alasan' => 'Dokumen masih dalam proses penandatanganan.',
            'surat_permohonan' => 'perpanjangan_waktu/surat.pdf',
        ]);
    }
}
