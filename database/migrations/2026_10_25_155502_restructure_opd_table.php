<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Struktur master OPD sesuai kamus data OPD (25 Okt 2026).
     *
     * id = id_opd. Kolom wajib tanpa nilai bawaan (kode_opd, jenis_opd, rumpun_urusan) dibuat nullable
     * di database agar migrasi aman bila sudah ada baris, dan diwajibkan lewat validasi (OpdRequest).
     */
    public function up(): void
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->renameColumn('nama', 'nama_opd');
        });

        Schema::table('opd', function (Blueprint $table) {
            $table->string('nama_opd', 150)->change();
            $table->string('singkatan', 20)->nullable()->change();
            $table->string('kode_opd', 8)->nullable()->unique();
            $table->string('kode_wilayah', 2)->default('82');
            $table->string('jenis_opd', 50)->nullable();
            $table->string('rumpun_urusan', 50)->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->date('tanggal_update')->nullable();
            $table->string('kode_urusan', 10)->nullable();
            $table->string('urusan_pemerintahan')->nullable();
            $table->string('induk_opd', 8)->nullable()->index();
        });

        DB::table('opd')->where('is_active', false)->update(['status' => 'Tidak Aktif']);
        DB::table('opd')->update(['tanggal_update' => DB::raw('CAST(COALESCE(updated_at, CURRENT_TIMESTAMP) AS DATE)')]);

        Schema::table('opd', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        DB::table('opd')->where('status', '!=', 'Aktif')->update(['is_active' => false]);

        Schema::table('opd', function (Blueprint $table) {
            $table->dropUnique(['kode_opd']);
            $table->dropIndex(['induk_opd']);
            $table->dropColumn(['kode_opd', 'kode_wilayah', 'jenis_opd', 'rumpun_urusan', 'status', 'tanggal_update', 'kode_urusan', 'urusan_pemerintahan', 'induk_opd']);
            $table->string('singkatan', 50)->nullable()->change();
            $table->string('nama_opd', 255)->change();
        });

        Schema::table('opd', function (Blueprint $table) {
            $table->renameColumn('nama_opd', 'nama');
        });
    }
};
