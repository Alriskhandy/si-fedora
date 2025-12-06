<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename persyaratan_dokumen_id ke master_kelengkapan_id
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            // Drop foreign key dan unique constraint dulu
            $table->dropForeign(['persyaratan_dokumen_id']);
            $table->dropUnique('permohonan_persyaratan_unique');
        });

        // Rename kolom
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->renameColumn('persyaratan_dokumen_id', 'master_kelengkapan_id');
        });

        // Tambah kembali foreign key dan unique constraint dengan nama baru
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->foreign('master_kelengkapan_id')
                ->references('id')
                ->on('master_kelengkapan_verifikasi')
                ->cascadeOnDelete();

            $table->unique(['permohonan_id', 'master_kelengkapan_id'], 'permohonan_kelengkapan_unique');
        });
    }

    public function down(): void
    {
        // Revert back
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->dropForeign(['master_kelengkapan_id']);
            $table->dropUnique('permohonan_kelengkapan_unique');
        });

        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->renameColumn('master_kelengkapan_id', 'persyaratan_dokumen_id');
        });

        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->foreign('persyaratan_dokumen_id')
                ->references('id')
                ->on('persyaratan_dokumen')
                ->cascadeOnDelete();

            $table->unique(['permohonan_id', 'persyaratan_dokumen_id'], 'permohonan_persyaratan_unique');
        });
    }
};
