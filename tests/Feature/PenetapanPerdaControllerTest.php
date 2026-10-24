<?php

namespace Tests\Feature;

use App\Models\DokumenTahapan;
use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenetapanPerdaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_document_is_stored_under_an_unguessable_name(): void
    {
        Storage::fake('dokumen');
        MasterTahapan::factory()->create(['id' => 7, 'nama_tahapan' => 'Penetapan PERDA']);
        $pemohon = User::factory()->withRole('pemohon')->create();
        $permohonan = Permohonan::factory()->for($pemohon, 'pemohon')->create();

        $response = $this->actingAs($pemohon)->post(route('penetapan-perda.upload', $permohonan), [
            'file' => UploadedFile::fake()->create('perda.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $dokumen = DokumenTahapan::where('permohonan_id', $permohonan->id)->sole();
        Storage::disk('dokumen')->assertExists($dokumen->file_path);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}\.pdf$/', basename($dokumen->file_path));
    }
}
