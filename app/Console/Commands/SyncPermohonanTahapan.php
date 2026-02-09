<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permohonan;
use App\Models\PermohonanTahapan;
use App\Models\MasterTahapan;

class SyncPermohonanTahapan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permohonan:sync-tahapan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permohonan tahapan untuk permohonan yang belum memiliki record tahapan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sync permohonan tahapan...');

        // Ambil semua permohonan
        $permohonanList = Permohonan::all();
        $tahapanPermohonan = MasterTahapan::where('urutan', 1)->first();

        if (!$tahapanPermohonan) {
            $this->error('Master tahapan pertama tidak ditemukan!');
            return 1;
        }

        $synced = 0;
        $skipped = 0;

        foreach ($permohonanList as $permohonan) {
            // Cek apakah sudah ada tahapan
            $existingTahapan = PermohonanTahapan::where('permohonan_id', $permohonan->id)->exists();

            if (!$existingTahapan) {
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
        $this->info("Sync selesai!");
        $this->info("Total diproses: " . $permohonanList->count());
        $this->info("Berhasil dibuat: {$synced}");
        $this->info("Dilewati (sudah ada): {$skipped}");

        return 0;
    }
}
