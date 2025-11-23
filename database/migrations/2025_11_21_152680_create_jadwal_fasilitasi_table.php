<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id');
            $table->foreignId('jenis_dokumen_id');
            $table->string('nama_kegiatan', 200);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('batas_permohonan')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('jadwal_fasilitasi', function (Blueprint $table) {
            $table->foreign('tahun_anggaran_id')->references('id')->on('tahun_anggaran')->cascadeOnDelete();
            $table->foreign('jenis_dokumen_id')->references('id')->on('jenis_dokumen')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
        
        // Tambahin index buat performance
        Schema::table('jadwal_fasilitasi', function (Blueprint $table) {
            $table->index(['tahun_anggaran_id', 'jenis_dokumen_id']);
            $table->index('status');
            $table->index('batas_permohonan');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_fasilitasi');
    }
};