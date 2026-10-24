<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PindahkanBerkasKePrivatTest extends TestCase
{
    private string $skTimDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skTimDir = sys_get_temp_dir().'/sk-tim-'.uniqid();
        File::ensureDirectoryExists($this->skTimDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->skTimDir);

        parent::tearDown();
    }

    public function test_moves_documents_and_sk_tim_to_private_disk_keeping_relative_paths(): void
    {
        Storage::fake('public');
        Storage::fake('dokumen');
        Storage::disk('public')->put('permohonan_dokumen/5/abc.pdf', 'isi');
        Storage::disk('public')->put('profile-photos/foto.jpg', 'foto');
        File::put($this->skTimDir.'/sk.pdf', 'sk');

        $this->artisan('berkas:pindah-ke-privat', ['--sk-tim-dir' => $this->skTimDir])->assertSuccessful();

        Storage::disk('dokumen')->assertExists('permohonan_dokumen/5/abc.pdf');
        Storage::disk('dokumen')->assertExists('sk-tim/sk.pdf');
        Storage::disk('public')->assertMissing('permohonan_dokumen/5/abc.pdf');
        Storage::disk('public')->assertExists('profile-photos/foto.jpg');
        Storage::disk('dokumen')->assertMissing('profile-photos/foto.jpg');
        $this->assertFileDoesNotExist($this->skTimDir.'/sk.pdf');
    }

    public function test_dry_run_moves_nothing(): void
    {
        Storage::fake('public');
        Storage::fake('dokumen');
        Storage::disk('public')->put('undangan/a.pdf', 'isi');

        $this->artisan('berkas:pindah-ke-privat', ['--dry-run' => true, '--sk-tim-dir' => $this->skTimDir])->assertSuccessful();

        Storage::disk('public')->assertExists('undangan/a.pdf');
        Storage::disk('dokumen')->assertMissing('undangan/a.pdf');
    }

    public function test_rollback_restores_files_to_public_locations(): void
    {
        Storage::fake('public');
        Storage::fake('dokumen');
        Storage::disk('dokumen')->put('undangan/a.pdf', 'isi');
        Storage::disk('dokumen')->put('sk-tim/sk.pdf', 'sk');

        $this->artisan('berkas:pindah-ke-privat', ['--rollback' => true, '--sk-tim-dir' => $this->skTimDir])->assertSuccessful();

        Storage::disk('public')->assertExists('undangan/a.pdf');
        $this->assertFileExists($this->skTimDir.'/sk.pdf');
        Storage::disk('dokumen')->assertMissing('sk-tim/sk.pdf');
    }
}
