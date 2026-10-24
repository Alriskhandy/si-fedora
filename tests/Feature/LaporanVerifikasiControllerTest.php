<?php

namespace Tests\Feature;

use App\Models\KabupatenKota;
use App\Models\LaporanVerifikasi;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanVerifikasiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_returns_pdf_of_verification_report(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $permohonan = Permohonan::factory()->create([
            'kab_kota_id' => KabupatenKota::factory()->create(['nama' => 'Kota Ternate'])->id,
            'tahun' => 2026,
        ]);
        LaporanVerifikasi::create([
            'permohonan_id' => $permohonan->id,
            'ringkasan_verifikasi' => 'Seluruh dokumen lengkap.',
            'status_kelengkapan' => 'lengkap',
            'total_dokumen' => 0,
            'jumlah_dokumen_verified' => 0,
            'jumlah_dokumen_revision' => 0,
            'dibuat_oleh' => $admin->id,
            'tanggal_laporan' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('laporan-verifikasi.download', $permohonan));

        $response->assertOk()->assertDownload('Laporan_Verifikasi_kota_ternate_2026.pdf');
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }
}
