<?php

namespace Tests\Feature;

use App\Models\Permohonan;
use App\Models\PerpanjanganWaktu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerpanjanganWaktuControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_extension_request_notifies_admin_peran(): void
    {
        Storage::fake('dokumen');
        Http::preventStrayRequests();
        Http::fake(['https://api.fonnte.com/send' => Http::response(['status' => true])]);
        $admin = User::factory()->withRole('admin_peran')->create(['no_hp' => '6281111111111']);
        $pemohon = User::factory()->withRole('pemohon')->create();
        $permohonan = Permohonan::factory()->for($pemohon, 'pemohon')->create();

        $this->actingAs($pemohon)->post(route('perpanjangan-waktu.store'), [
            'permohonan_id' => $permohonan->id,
            'alasan' => 'Dokumen pendukung masih dalam proses penandatanganan pimpinan.',
            'surat_permohonan' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('permohonan.show', $permohonan));

        $perpanjangan = PerpanjanganWaktu::where('permohonan_id', $permohonan->id)->sole();
        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $admin->id,
            'title' => 'Pengajuan Perpanjangan Waktu',
            'model_type' => PerpanjanganWaktu::class,
            'model_id' => $perpanjangan->id,
        ]);
        Http::assertSent(fn (Request $request) => $request['target'] === '6281111111111');
    }
}
