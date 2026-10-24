<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PindahkanBerkasKePrivat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'berkas:pindah-ke-privat
        {--dry-run : Hanya tampilkan jumlah berkas yang akan dipindah}
        {--rollback : Kembalikan berkas dari disk dokumen ke lokasi publik lama}
        {--sk-tim-dir= : Lokasi folder SK Tim lama (default: public/sk-tim)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindahkan dokumen dari disk public dan public/sk-tim ke disk privat "dokumen" dengan path relatif yang sama';

    /**
     * Folder di disk public yang tetap publik.
     *
     * @var list<string>
     */
    private const TETAP_PUBLIK = ['profile-photos'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $publik = Storage::disk('public');
        $dokumen = Storage::disk('dokumen');
        $skTim = Storage::build([
            'driver' => 'local',
            'root' => $this->option('sk-tim-dir') ?: public_path('sk-tim'),
        ]);

        if ($this->option('rollback')) {
            $hasil = [
                ['public/sk-tim', $this->pindahkan($dokumen, $skTim, $this->berkasDiFolder($dokumen, 'sk-tim'), $isDryRun, stripPrefix: 'sk-tim/')],
                ['storage/app/public', $this->pindahkan($dokumen, $publik, $this->berkasKecuali($dokumen, ['sk-tim']), $isDryRun)],
            ];
        } else {
            $hasil = [
                ['storage/app/public', $this->pindahkan($publik, $dokumen, $this->berkasKecuali($publik, self::TETAP_PUBLIK), $isDryRun)],
                ['public/sk-tim', $this->pindahkan($skTim, $dokumen, $skTim->allFiles(), $isDryRun, addPrefix: 'sk-tim/')],
            ];
        }

        $this->table(['Sumber', $isDryRun ? 'Akan dipindah' : 'Dipindah', 'Dilewati (tujuan sudah ada)'], array_map(
            fn (array $baris) => [$baris[0], $baris[1]['dipindah'], $baris[1]['dilewati']],
            $hasil,
        ));

        if ($isDryRun) {
            $this->info('Dry run: tidak ada berkas yang dipindah.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $paths
     * @return array{dipindah: int, dilewati: int}
     */
    private function pindahkan(Filesystem $dari, Filesystem $ke, array $paths, bool $isDryRun, string $addPrefix = '', string $stripPrefix = ''): array
    {
        $dipindah = 0;
        $dilewati = 0;

        foreach ($paths as $path) {
            $tujuan = $addPrefix.($stripPrefix !== '' && str_starts_with($path, $stripPrefix) ? substr($path, strlen($stripPrefix)) : $path);

            if ($ke->exists($tujuan)) {
                $this->warn("Dilewati, sudah ada di tujuan: {$tujuan}");
                $dilewati++;

                continue;
            }

            $dipindah++;

            if ($isDryRun) {
                continue;
            }

            $stream = $dari->readStream($path);
            $ke->writeStream($tujuan, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $dari->delete($path);
        }

        return ['dipindah' => $dipindah, 'dilewati' => $dilewati];
    }

    /**
     * @param  list<string>  $folderDikecualikan
     * @return list<string>
     */
    private function berkasKecuali(Filesystem $disk, array $folderDikecualikan): array
    {
        return array_values(array_filter(
            $disk->allFiles(),
            fn (string $path) => ! in_array(explode('/', $path)[0], $folderDikecualikan, true) && basename($path) !== '.gitignore',
        ));
    }

    /**
     * @return list<string>
     */
    private function berkasDiFolder(Filesystem $disk, string $folder): array
    {
        return $disk->allFiles($folder);
    }
}
