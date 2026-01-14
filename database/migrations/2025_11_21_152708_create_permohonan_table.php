<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kab_kota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $table->foreignId('jenis_dokumen_id')->constrained('master_jenis_dokumen')->cascadeOnDelete();
            
            $table->integer('tahun');
            $table->enum('status_akhir', ['belum', 'proses', 'revisi', 'selesai'])->default('belum');
            
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['user_id', 'tahun', 'jenis_dokumen_id']);
            $table->index(['kab_kota_id', 'tahun']);
            $table->index(['status_akhir']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};
