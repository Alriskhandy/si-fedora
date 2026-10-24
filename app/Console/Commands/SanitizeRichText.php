<?php

namespace App\Console\Commands;

use App\Models\HasilFasilitasiForm;
use App\Models\HasilFasilitasiRekomendasi;
use App\Models\HasilFasilitasiSistematika;
use App\Models\HasilFasilitasiUrusan;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

/**
 * Menyanitasi ulang konten rich text hasil fasilitasi yang tersimpan sebelum cast SanitizedHtml diterapkan.
 *
 * Kolom yang diproses didaftarkan di konstanta COLUMNS; tambahkan kolom baru di sini bila menambah kolom rich text.
 * Dijalankan sekali saat rilis Fase 0, atau setelah whitelist sanitizer diubah.
 */
class SanitizeRichText extends Command
{
    /**
     * Nama dan argumen command.
     *
     * @var string
     */
    protected $signature = 'richtext:sanitize {--dry-run : Hanya tampilkan jumlah record yang akan berubah}';

    /**
     * Deskripsi yang tampil di `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Sanitasi ulang konten rich text hasil fasilitasi yang tersimpan sebelum cast SanitizedHtml diterapkan';

    /**
     * Kolom rich text per model.
     *
     * @var array<class-string<Model>, string>
     */
    private const COLUMNS = [
        HasilFasilitasiForm::class => 'catatan',
        HasilFasilitasiRekomendasi::class => 'catatan',
        HasilFasilitasiSistematika::class => 'catatan_penyempurnaan',
        HasilFasilitasiUrusan::class => 'catatan_masukan',
    ];

    /**
     * Sanitasi setiap kolom rich text dan laporkan jumlah record yang berubah.
     */
    public function handle(HtmlSanitizer $sanitizer): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $rows = [];

        foreach (self::COLUMNS as $modelClass => $column) {
            $changed = 0;

            $modelClass::query()
                ->whereNotNull($column)
                ->chunkById(200, function ($records) use ($sanitizer, $column, $isDryRun, &$changed): void {
                    foreach ($records as $record) {
                        $original = (string) $record->getRawOriginal($column);
                        $sanitized = $sanitizer->sanitize($original);

                        if ($sanitized === $original) {
                            continue;
                        }

                        $changed++;

                        if (! $isDryRun) {
                            $record->newQuery()->toBase()->where($record->getKeyName(), $record->getKey())->update([$column => $sanitized]);
                        }
                    }
                });

            $rows[] = [class_basename($modelClass), $column, $changed];
        }

        $this->table(['Model', 'Kolom', $isDryRun ? 'Akan berubah' : 'Diubah'], $rows);

        if ($isDryRun) {
            $this->info('Dry run: tidak ada data yang diubah.');
        }

        return self::SUCCESS;
    }
}
