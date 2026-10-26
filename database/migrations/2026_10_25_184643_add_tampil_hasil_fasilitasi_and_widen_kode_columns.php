<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - master_urusan.tampil_hasil_fasilitasi: hanya urusan yang aktif di sini yang muncul di bagian
     *   urusan hasil fasilitasi; bawaan aktif untuk kelompok 1–3 (wajib dasar, wajib non dasar, pilihan).
     * - Kolom kode OPD diperlebar: kode OPD bisa 4 segmen (82.01.01.01 untuk Biro/UPTD) dan
     *   kode urusan yang diampu OPD bisa lebih dari satu (dipisah ;), sesuai data CSV master.
     */
    public function up(): void
    {
        Schema::table('master_urusan', function (Blueprint $table) {
            $table->boolean('tampil_hasil_fasilitasi')->default(false);
        });

        DB::table('master_urusan')->whereIn('kode_kelompok', ['1', '2', '3'])->update(['tampil_hasil_fasilitasi' => true]);

        Schema::table('opd', function (Blueprint $table) {
            $table->string('kode_opd', 20)->nullable()->change();
            $table->string('induk_opd', 20)->nullable()->change();
            $table->string('kode_urusan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->string('kode_opd', 8)->nullable()->change();
            $table->string('induk_opd', 8)->nullable()->change();
            $table->string('kode_urusan', 10)->nullable()->change();
        });

        Schema::table('master_urusan', function (Blueprint $table) {
            $table->dropColumn('tampil_hasil_fasilitasi');
        });
    }
};
