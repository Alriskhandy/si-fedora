<?php

namespace App\Console\Commands;

use App\Models\JadwalFasilitasi;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\User;
use App\Services\PermohonanNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RemindPermohonanDeadline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permohonan:remind-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat H-3, H-2, H-1 ke pemohon yang belum membuat permohonan sebelum batas waktu permohonan berakhir';

    /**
     * Execute the console command.
     */
    public function handle(PermohonanNotificationService $notificationService)
    {
        $today = Carbon::today();

        $jadwalList = JadwalFasilitasi::published()
            ->whereDate('batas_permohonan', '>=', $today)
            ->whereDate('batas_permohonan', '<=', $today->copy()->addDays(3))
            ->get();

        if ($jadwalList->isEmpty()) {
            $this->info('Tidak ada jadwal fasilitasi dengan batas permohonan dalam 3 hari ke depan.');
            return Command::SUCCESS;
        }

        $totalSent = 0;

        foreach ($jadwalList as $jadwal) {
            $sisaHari = $today->diffInDays(Carbon::parse($jadwal->batas_permohonan), false);

            if ($sisaHari < 1 || $sisaHari > 3) {
                continue;
            }

            $pemohonIdsSudahMengajukan = Permohonan::where('jadwal_fasilitasi_id', $jadwal->id)
                ->pluck('user_id');

            $pemohonList = User::role('pemohon')
                ->whereNotNull('kabupaten_kota_id')
                ->whereNotIn('id', $pemohonIdsSudahMengajukan)
                ->get();

            foreach ($pemohonList as $pemohon) {
                $sudahDiingatkanHariIni = Notifikasi::where('user_id', $pemohon->id)
                    ->where('model_type', JadwalFasilitasi::class)
                    ->where('model_id', $jadwal->id)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($sudahDiingatkanHariIni) {
                    continue;
                }

                $notificationService->notifyPermohonanDeadlineReminder($jadwal, $pemohon, $sisaHari);
                $totalSent++;
            }
        }

        $this->info("Pengingat batas waktu permohonan berhasil dikirim ke {$totalSent} pemohon.");

        return Command::SUCCESS;
    }
}
