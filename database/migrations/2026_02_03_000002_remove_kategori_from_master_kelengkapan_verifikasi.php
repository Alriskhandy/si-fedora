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
            $table->dropColumn('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->enum('kategori', ['surat_permohonan', 'kelengkapan_verifikasi'])
                  ->after('nama_dokumen')
                  ->default('kelengkapan_verifikasi');
        });
    }
};
