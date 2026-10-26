<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori lama → kode kelompok urusan.
     *
     * @var array<string, int>
     */
    private const KATEGORI_KE_KELOMPOK = ['wajib_dasar' => 1, 'wajib_non_dasar' => 2, 'pilihan' => 3];

    /**
     * Nama kelompok dan rumpun untuk kelompok data lama (salinan tetap, tidak mengikuti model).
     *
     * @var array<int, array{nama: string, rumpun: string}>
     */
    private const KELOMPOK = [
        1 => ['nama' => 'Urusan Pemerintahan Wajib yang Berkaitan dengan Pelayanan Dasar', 'rumpun' => 'Wajib Pelayanan Dasar'],
        2 => ['nama' => 'Urusan Pemerintahan Wajib yang Tidak Berkaitan dengan Pelayanan Dasar', 'rumpun' => 'Wajib Non Pelayanan Dasar'],
        3 => ['nama' => 'Urusan Pemerintahan Pilihan', 'rumpun' => 'Pilihan'],
    ];

    /**
     * Struktur master urusan sesuai kamus data urusan (25 Okt 2026).
     *
     * id = id_urusan. Kode urusan data lama diisi dari kategori + urutan (urutan 1–32 sama dengan
     * nomor bidang resmi, mis. Pendidikan = 1.01, Transmigrasi = 3.32), lalu kategori dan urutan dihapus.
     */
    public function up(): void
    {
        Schema::table('master_urusan', function (Blueprint $table) {
            $table->string('kode_urusan', 5)->nullable();
            $table->string('kode_kelompok', 1)->nullable();
            $table->string('nama_kelompok', 100)->nullable();
            $table->string('rumpun_urusan', 50)->nullable();
            $table->string('kode_opd_pengampu')->nullable();
            $table->string('opd_pengampu')->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->date('tanggal_update')->nullable();
        });

        foreach (DB::table('master_urusan')->orderBy('id')->get() as $row) {
            $kelompok = self::KATEGORI_KE_KELOMPOK[$row->kategori] ?? 3;
            $bidang = str_pad((string) ($row->urutan ?? $row->id), 2, '0', STR_PAD_LEFT);

            DB::table('master_urusan')->where('id', $row->id)->update([
                'kode_urusan' => $kelompok.'.'.$bidang,
                'kode_kelompok' => (string) $kelompok,
                'nama_kelompok' => self::KELOMPOK[$kelompok]['nama'],
                'rumpun_urusan' => self::KELOMPOK[$kelompok]['rumpun'],
                'tanggal_update' => now()->toDateString(),
            ]);
        }

        Schema::table('master_urusan', function (Blueprint $table) {
            $table->string('kode_urusan', 5)->nullable(false)->unique()->change();
            $table->string('kode_kelompok', 1)->nullable(false)->change();
            $table->string('nama_kelompok', 100)->nullable(false)->change();
            $table->string('rumpun_urusan', 50)->nullable(false)->change();
            $table->date('tanggal_update')->nullable(false)->change();
            $table->string('nama_urusan', 150)->change();
            $table->dropColumn(['kategori', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_urusan', function (Blueprint $table) {
            $table->string('kategori')->default('pilihan');
            $table->integer('urutan')->nullable();
        });

        $kelompokKeKategori = array_flip(self::KATEGORI_KE_KELOMPOK);
        foreach (DB::table('master_urusan')->get() as $row) {
            DB::table('master_urusan')->where('id', $row->id)->update([
                'kategori' => $kelompokKeKategori[(int) $row->kode_kelompok] ?? 'pilihan',
                'urutan' => (int) substr($row->kode_urusan, 2),
            ]);
        }

        Schema::table('master_urusan', function (Blueprint $table) {
            $table->dropUnique(['kode_urusan']);
            $table->dropColumn(['kode_urusan', 'kode_kelompok', 'nama_kelompok', 'rumpun_urusan', 'kode_opd_pengampu', 'opd_pengampu', 'status', 'tanggal_update']);
            $table->string('nama_urusan', 255)->change();
        });
    }
};
