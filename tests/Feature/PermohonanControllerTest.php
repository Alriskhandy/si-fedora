<?php

namespace Tests\Feature;

use App\Enums\StatusPermohonan;
use App\Models\MasterKelengkapanVerifikasi;
use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanTahapan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PermohonanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_complete_permohonan_starts_verification_stage(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://api.fonnte.com/*' => Http::response(['status' => true])]);
        $tahapanPermohonan = MasterTahapan::factory()->create(['nama_tahapan' => 'Permohonan', 'urutan' => 1]);
        $tahapanVerifikasi = MasterTahapan::factory()->create(['nama_tahapan' => 'Verifikasi', 'urutan' => 2]);
        $pemohon = User::factory()->withRole('pemohon')->create();
        $permohonan = Permohonan::factory()->for($pemohon, 'pemohon')->create(['status_akhir' => 'belum']);
        $this->createDokumen($permohonan, isAda: true);

        $response = $this->actingAs($pemohon)->post(route('permohonan.submit', $permohonan));

        $response->assertRedirect(route('permohonan.show', $permohonan))->assertSessionHas('success');
        $permohonan->refresh();
        $this->assertSame(StatusPermohonan::Proses, $permohonan->status_akhir);
        $this->assertNotNull($permohonan->submitted_at);
        $this->assertSame(StatusPermohonan::Selesai, PermohonanTahapan::where('permohonan_id', $permohonan->id)->where('tahapan_id', $tahapanPermohonan->id)->value('status'));
        $this->assertSame(StatusPermohonan::Proses, PermohonanTahapan::where('permohonan_id', $permohonan->id)->where('tahapan_id', $tahapanVerifikasi->id)->value('status'));
    }

    public function test_submitting_with_incomplete_documents_is_rejected(): void
    {
        $pemohon = User::factory()->withRole('pemohon')->create();
        $permohonan = Permohonan::factory()->for($pemohon, 'pemohon')->create(['status_akhir' => 'belum']);
        $this->createDokumen($permohonan, isAda: false);

        $response = $this->actingAs($pemohon)->post(route('permohonan.submit', $permohonan));

        $response->assertSessionHas('error', 'Tidak dapat mengirim permohonan. Harap lengkapi semua dokumen persyaratan terlebih dahulu.');
        $this->assertSame(StatusPermohonan::Belum, $permohonan->fresh()->status_akhir);
    }

    public function test_pemohon_cannot_submit_permohonan_of_another_account(): void
    {
        $pemohonLain = User::factory()->withRole('pemohon')->create();
        $permohonan = Permohonan::factory()->create(['status_akhir' => 'belum']);

        $this->actingAs($pemohonLain)->post(route('permohonan.submit', $permohonan))->assertForbidden();

        $this->assertSame(StatusPermohonan::Belum, $permohonan->fresh()->status_akhir);
    }

    public function test_pemohon_cannot_delete_permohonan_of_another_account(): void
    {
        $pemohonLain = User::factory()->withRole('pemohon')->create();
        $permohonan = Permohonan::factory()->create(['status_akhir' => 'belum']);

        $this->actingAs($pemohonLain)->delete(route('permohonan.destroy', $permohonan))->assertForbidden();

        $this->assertNotSoftDeleted($permohonan);
    }

    private function createDokumen(Permohonan $permohonan, bool $isAda): void
    {
        $kelengkapan = MasterKelengkapanVerifikasi::create(['nama_dokumen' => 'Surat Permohonan']);

        PermohonanDokumen::create([
            'permohonan_id' => $permohonan->id,
            'master_kelengkapan_id' => $kelengkapan->id,
            'is_ada' => $isAda,
        ]);
    }
}
