<?php

namespace Tests\Feature;

use App\Models\DokumenTahapan;
use App\Models\KabupatenKota;
use App\Models\MasterJenisDokumen;
use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArsipControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemohon_is_forbidden_from_archive_of_other_kabupaten_kota(): void
    {
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => KabupatenKota::factory()->create()->id]);
        $permohonanLain = Permohonan::factory()->create();

        $this->actingAs($pemohon)->get(route('arsip.show', $permohonanLain))->assertForbidden();
    }

    public function test_archive_detail_exposes_file_extension_for_preview(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();
        $permohonan = Permohonan::factory()->create();
        DokumenTahapan::create([
            'permohonan_id' => $permohonan->id,
            'tahapan_id' => MasterTahapan::factory()->create(['nama_tahapan' => 'Tindak Lanjut Hasil'])->id,
            'user_id' => $permohonan->user_id,
            'nama_dokumen' => 'Dokumen Tindak Lanjut',
            'file_path' => 'dokumen-tahapan/tindak-lanjut/acak.PDF',
        ]);

        $response = $this->actingAs($admin)->get(route('arsip.show', $permohonan));

        $response->assertOk()->assertSee('data-file-ext="pdf"', false);
    }

    public function test_archive_list_only_shows_permohonan_of_own_kabupaten_kota(): void
    {
        $jenisDokumen = MasterJenisDokumen::factory()->create();
        $kabKotaSendiri = KabupatenKota::factory()->create(['nama' => 'Kota Sendiri']);
        $kabKotaLain = KabupatenKota::factory()->create(['nama' => 'Kabupaten Lain']);
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => $kabKotaSendiri->id]);
        Permohonan::factory()->create(['kabupaten_kota_id' => $kabKotaSendiri->id, 'jenis_dokumen_id' => $jenisDokumen->id]);
        Permohonan::factory()->create(['kabupaten_kota_id' => $kabKotaLain->id, 'jenis_dokumen_id' => $jenisDokumen->id]);

        $response = $this->actingAs($pemohon)->get(route('arsip.listByJenis', $jenisDokumen->id));

        $response->assertOk()
            ->assertSee('Kota Sendiri')
            ->assertDontSee('Kabupaten Lain');
    }
}
