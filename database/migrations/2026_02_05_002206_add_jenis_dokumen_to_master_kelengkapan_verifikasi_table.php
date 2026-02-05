<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            // Hapus kolom kategori karena tidak diperlukan lagi
            $table->dropColumn('kategori');
            
            // Tambah kolom jenis_dokumen_id
            $table->foreignId('jenis_dokumen_id')->nullable()->after('tahapan_id')->constrained('master_jenis_dokumen')->nullOnDelete()->comment('Relasi ke jenis dokumen (RKPD, KUA-PPAS, dll)');
            $table->index('jenis_dokumen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->dropForeign(['jenis_dokumen_id']);
            $table->dropIndex(['jenis_dokumen_id']);
            $table->dropColumn('jenis_dokumen_id');
            
            // Kembalikan kolom kategori
            $table->enum('kategori', ['surat_permohonan', 'kelengkapan_verifikasi'])->default('kelengkapan_verifikasi')->after('nama_dokumen');
        });
    }
};
