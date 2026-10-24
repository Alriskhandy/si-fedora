<?php

namespace App\Console\Commands;

use App\Models\MasterTahapan;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use Illuminate\Console\Command;

/**
 * Melengkapi record tahapan pertama (PermohonanTahapan) untuk permohonan lama yang belum memilikinya.
 *
 * Perbaikan data sekali jalan; aman diulang (permohonan yang sudah punya tahapan dilewati).
 */
class SyncPermohonanTahapan extends Command
{
    /**
     * Nama dan argumen command.
     *
     * @var string
     */
    protected $signature = 'permohonan:sync-tahapan';

    /**
     * Deskripsi yang tampil di `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Sync permohonan tahapan untuk permohonan yang belum memiliki record tahapan';

    /**
     * Buat tahapan pertama berstatus "proses" untuk permohonan yang belum punya record tahapan.
     */
    public function handle()
    {
        $this->info('Memulai sync permohonan tahapan...');

        // Ambil semua permohonan
        $permohonanList = Permohonan::all();
        $tahapanPermohonan = MasterTahapan::where('urutan', 1)->first();

        if (! $tahapanPermohonan) {
            $this->error('Master tahapan pertama tidak ditemukan!');

            return 1;
        }

        $synced = 0;
        $skipped = 0;

        foreach ($permohonanList as $permohonan) {
            // Cek apakah sudah ada tahapan
            $existingTahapan = PermohonanTahapan::where('permohonan_id', $permohonan->id)->exists();

            if (! $existingTahapan) {
                // Buat record tahapan pertama
                PermohonanTahapan::create([
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $tahapanPermohonan->id,
                    'status' => 'proses',
                    'created_at' => $permohonan->created_at,
                    'updated_at' => $permohonan->created_at,
                ]);

                $synced++;
                $this->line("✓ Permohonan ID {$permohonan->id} - Tahapan berhasil dibuat");
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Sync selesai!');
        $this->info('Total diproses: '.$permohonanList->count());
        $this->info("Berhasil dibuat: {$synced}");
        $this->info("Dilewati (sudah ada): {$skipped}");

        return 0;
    }
}
