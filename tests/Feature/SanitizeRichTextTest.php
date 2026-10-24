<?php

namespace Tests\Feature;

use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SanitizeRichTextTest extends TestCase
{
    use RefreshDatabase;

    private const LEGACY_HTML = '<p>Catatan lama<script>alert(1)</script></p>';

    public function test_command_sanitizes_content_saved_before_the_cast_existed(): void
    {
        $formId = $this->insertLegacyForm();

        $this->artisan('richtext:sanitize')->assertSuccessful();

        $stored = DB::table('hasil_fasilitasi_form')->where('id', $formId)->value('catatan');
        $this->assertStringContainsString('Catatan lama', $stored);
        $this->assertStringNotContainsString('<script', $stored);
    }

    public function test_dry_run_does_not_change_stored_content(): void
    {
        $formId = $this->insertLegacyForm();

        $this->artisan('richtext:sanitize', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(self::LEGACY_HTML, DB::table('hasil_fasilitasi_form')->where('id', $formId)->value('catatan'));
    }

    /**
     * Simpan HTML mentah langsung ke tabel, seperti data yang tersimpan sebelum cast diterapkan.
     */
    private function insertLegacyForm(): int
    {
        $hasilFasilitasi = HasilFasilitasi::factory()->create();

        $form = HasilFasilitasiForm::create([
            'hasil_fasilitasi_id' => $hasilFasilitasi->id,
            'user_id' => $hasilFasilitasi->dibuat_oleh,
            'catatan' => 'sementara',
        ]);

        DB::table('hasil_fasilitasi_form')->where('id', $form->id)->update(['catatan' => self::LEGACY_HTML]);

        return $form->id;
    }
}
