<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seragamkan nama kolom kab/kota dengan tabel lain (Fase 4 butir B). Index dan foreign key ikut diganti
     * namanya agar sesuai konvensi Laravel untuk kolom baru.
     */
    public function up(): void
    {
        $this->rename('kab_kota_id', 'kabupaten_kota_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rename('kabupaten_kota_id', 'kab_kota_id');
    }

    private function rename(string $from, string $to): void
    {
        Schema::table('permohonan', function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
            $table->renameIndex("permohonan_{$from}_tahun_index", "permohonan_{$to}_tahun_index");
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE permohonan RENAME CONSTRAINT permohonan_{$from}_foreign TO permohonan_{$to}_foreign");
        }
    }
};
