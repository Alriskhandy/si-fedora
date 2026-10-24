<?php

namespace Tests\Feature;

use App\Models\DokumenTahapan;
use App\Models\KabupatenKota;
use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BerkasControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_page(): void
    {
        Storage::fake('dokumen');
        $dokumen = $this->createDokumenTahapan(Permohonan::factory()->create());

        $this->get(route('berkas.show', ['dokumen-tahapan', $dokumen->id]))->assertRedirect(route('home'));
    }

    public function test_pemohon_of_other_kabupaten_kota_is_forbidden(): void
    {
        Storage::fake('dokumen');
        $dokumen = $this->createDokumenTahapan(Permohonan::factory()->create());
        $pemohonLain = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => KabupatenKota::factory()->create()->id]);

        $this->actingAs($pemohonLain)
            ->get(route('berkas.show', ['dokumen-tahapan', $dokumen->id]))
            ->assertForbidden();
    }

    public function test_owner_downloads_file_under_its_original_name(): void
    {
        Storage::fake('dokumen');
        $kabKota = KabupatenKota::factory()->create();
        $pemohon = User::factory()->withRole('pemohon')->create(['kabupaten_kota_id' => $kabKota->id]);
        $dokumen = $this->createDokumenTahapan(Permohonan::factory()->create(['kab_kota_id' => $kabKota->id]));

        $response = $this->actingAs($pemohon)->get(route('berkas.show', ['dokumen-tahapan', $dokumen->id, 'unduh' => 1]));

        $response->assertOk()->assertDownload('Berita Acara.pdf');
    }

    public function test_unknown_file_type_returns_404(): void
    {
        $admin = User::factory()->withRole('admin_peran')->create();

        $this->actingAs($admin)->get(route('berkas.show', ['users', 1]))->assertNotFound();
    }

    public function test_missing_file_returns_404(): void
    {
        Storage::fake('dokumen');
        $admin = User::factory()->withRole('admin_peran')->create();
        $dokumen = $this->createDokumenTahapan(Permohonan::factory()->create());
        Storage::disk('dokumen')->delete($dokumen->file_path);

        $this->actingAs($admin)->get(route('berkas.show', ['dokumen-tahapan', $dokumen->id]))->assertNotFound();
    }

    private function createDokumenTahapan(Permohonan $permohonan): DokumenTahapan
    {
        Storage::disk('dokumen')->put('dokumen-tahapan/pelaksanaan/acak.pdf', '%PDF-1.4');

        return DokumenTahapan::create([
            'permohonan_id' => $permohonan->id,
            'tahapan_id' => MasterTahapan::factory()->create()->id,
            'user_id' => $permohonan->user_id,
            'nama_dokumen' => 'Berita Acara',
            'file_path' => 'dokumen-tahapan/pelaksanaan/acak.pdf',
            'file_name' => 'Berita Acara.pdf',
        ]);
    }
}
