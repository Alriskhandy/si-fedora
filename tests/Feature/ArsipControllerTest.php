<?php

namespace Tests\Feature;

use App\Models\KabupatenKota;
use App\Models\MasterJenisDokumen;
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

    public function test_archive_list_only_shows_permohonan_of_own_kabupaten_kota(): void
    {
        $jenisDokumen = MasterJenisDokumen::factory()->create();
        $kabKotaSendiri = KabupatenKota::factory()->create(['nama' => 'Kota Sendiri']);
        $kabKotaLain = KabupatenKota::factory()->create(['nama' => 'Kabupaten Lain']);
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => $kabKotaSendiri->id]);
        Permohonan::factory()->create(['kab_kota_id' => $kabKotaSendiri->id, 'jenis_dokumen_id' => $jenisDokumen->id]);
        Permohonan::factory()->create(['kab_kota_id' => $kabKotaLain->id, 'jenis_dokumen_id' => $jenisDokumen->id]);

        $response = $this->actingAs($pemohon)->get(route('arsip.listByJenis', $jenisDokumen->id));

        $response->assertOk()
            ->assertSee('Kota Sendiri')
            ->assertDontSee('Kabupaten Lain');
    }
}
