<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sekali jalan: isi opd_urusan dari opd.kode_urusan untuk OPD yang sudah ada.
     * Setelah ini sinkronisasi berjalan otomatis lewat model Opd dan MasterUrusan.
     */
    public function up(): void
    {
        $urusanPerKode = DB::table('master_urusan')->pluck('id', 'kode_urusan');

        foreach (DB::table('opd')->whereNotNull('kode_urusan')->get(['id', 'kode_urusan']) as $opd) {
            $kodes = array_filter(array_map('trim', explode(';', $opd->kode_urusan)));

            DB::table('opd_urusan')->where('opd_id', $opd->id)->delete();
            DB::table('opd_urusan')->insert(
                collect($kodes)
                    ->map(fn (string $kode) => $urusanPerKode[$kode] ?? null)
                    ->filter()
                    ->unique()
                    ->map(fn (int $urusanId) => ['opd_id' => $opd->id, 'master_urusan_id' => $urusanId])
                    ->values()
                    ->all()
            );
        }
    }

    /**
     * Data hasil sinkronisasi dibiarkan; tidak ada perubahan struktur yang perlu dibalik.
     */
    public function down(): void
    {
        //
    }
};
