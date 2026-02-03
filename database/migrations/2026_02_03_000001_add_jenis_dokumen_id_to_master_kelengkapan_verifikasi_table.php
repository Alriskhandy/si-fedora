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
            // Menambahkan kolom jenis_dokumen_id untuk relasi dengan master_jenis_dokumen
            $table->foreignId('jenis_dokumen_id')
                  ->nullable()
                  ->after('tahapan_id')
                  ->constrained('master_jenis_dokumen')
                  ->onDelete('cascade');
            
            // Menambahkan index untuk performa query
            $table->index(['jenis_dokumen_id', 'tahapan_id']);
            $table->index(['jenis_dokumen_id', 'kategori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->dropForeign(['jenis_dokumen_id']);
            $table->dropIndex(['jenis_dokumen_id', 'tahapan_id']);
            $table->dropIndex(['jenis_dokumen_id', 'kategori']);
            $table->dropColumn('jenis_dokumen_id');
        });
    }
};
