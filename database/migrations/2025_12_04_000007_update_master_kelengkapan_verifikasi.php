<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom kategori untuk membedakan surat_permohonan vs kelengkapan_verifikasi
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->enum('kategori', ['surat_permohonan', 'kelengkapan_verifikasi'])
                ->default('kelengkapan_verifikasi')
                ->after('nama_dokumen');

            $table->foreignId('tahapan_id')
                ->nullable()
                ->after('kategori')
                ->constrained('master_tahapan')
                ->nullOnDelete()
                ->comment('Kelengkapan untuk tahapan tertentu (null = berlaku untuk semua)');

            $table->integer('urutan')->default(0)->after('wajib');

            // Index
            $table->index(['kategori', 'tahapan_id']);
            $table->index(['wajib', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->dropForeign(['tahapan_id']);
            $table->dropColumn(['kategori', 'tahapan_id', 'urutan']);
        });
    }
};
