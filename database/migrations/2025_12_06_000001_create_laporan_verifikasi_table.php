<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->text('ringkasan_verifikasi')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->enum('status_kelengkapan', ['lengkap', 'tidak_lengkap'])->default('lengkap');
            $table->integer('jumlah_dokumen_verified')->default(0);
            $table->integer('jumlah_dokumen_revision')->default(0);
            $table->integer('total_dokumen')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_laporan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_verifikasi');
    }
};
