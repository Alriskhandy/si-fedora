<?php

namespace Tests\Feature;

use App\Models\HasilFasilitasi;
use App\Models\HasilFasilitasiForm;
use App\Models\HasilFasilitasiRekomendasi;
use App\Models\HasilFasilitasiSistematika;
use App\Models\HasilFasilitasiUrusan;
use App\Models\MasterUrusan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SanitizedHtmlTest extends TestCase
{
    use RefreshDatabase;

    private const XSS_PAYLOAD = '<p onclick="steal()">Catatan<script>alert(1)</script></p><img src=x onerror=alert(1)>';

    /**
     * @return array<string, array{class-string<Model>, string}>
     */
    public static function richTextColumns(): array
    {
        return [
            'form' => [HasilFasilitasiForm::class, 'catatan'],
            'rekomendasi' => [HasilFasilitasiRekomendasi::class, 'catatan'],
            'sistematika' => [HasilFasilitasiSistematika::class, 'catatan_penyempurnaan'],
            'urusan' => [HasilFasilitasiUrusan::class, 'catatan_masukan'],
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    #[DataProvider('richTextColumns')]
    public function test_script_and_event_handlers_are_removed_before_saving(string $modelClass, string $column): void
    {
        $record = $this->createRecord($modelClass, $column, self::XSS_PAYLOAD);

        $stored = (string) $record->fresh()->getRawOriginal($column);

        $this->assertStringContainsString('Catatan', $stored);
        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('alert(1)', $stored);
        $this->assertStringNotContainsString('onclick', $stored);
        $this->assertStringNotContainsString('onerror', $stored);
        $this->assertStringNotContainsString('<img', $stored);
    }

    public function test_formatting_produced_by_the_editor_is_kept(): void
    {
        $html = '<p><strong>Tebal</strong> <em>miring</em> <u>garis</u> <s>coret</s></p>'
            .'<ul><li>satu</li></ul><ol><li>dua</li></ol>'
            .'<p><span style="color: red;">merah</span> H<sub>2</sub>O x<sup>2</sup><br /></p>';

        $record = $this->createRecord(HasilFasilitasiForm::class, 'catatan', $html);

        $stored = (string) $record->fresh()->catatan;

        foreach (['<strong>Tebal</strong>', '<em>miring</em>', '<u>garis</u>', '<s>coret</s>', '<ul><li>satu</li></ul>', '<ol><li>dua</li></ol>', '<span style="color: red;">merah</span>', '<sub>2</sub>', '<sup>2</sup>', '<br'] as $fragment) {
            $this->assertStringContainsString($fragment, $stored);
        }
    }

    public function test_text_inside_unsupported_elements_is_kept_without_the_element(): void
    {
        $record = $this->createRecord(HasilFasilitasiForm::class, 'catatan', '<div>Lihat <a href="https://contoh.test">tautan</a></div>');

        $stored = (string) $record->fresh()->catatan;

        $this->assertStringContainsString('Lihat tautan', $stored);
        $this->assertStringNotContainsString('<a', $stored);
        $this->assertStringNotContainsString('<div', $stored);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function createRecord(string $modelClass, string $column, string $html): Model
    {
        $hasilFasilitasi = HasilFasilitasi::factory()->create();

        $attributes = [
            'hasil_fasilitasi_id' => $hasilFasilitasi->id,
            'user_id' => $hasilFasilitasi->dibuat_oleh,
            $column => $html,
        ];

        if ($modelClass === HasilFasilitasiUrusan::class) {
            $attributes['master_urusan_id'] = MasterUrusan::factory()->create()->id;
        }

        return $modelClass::create($attributes);
    }
}
