<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dokumen yang diunggah pada tiap tahapan
        Schema::create('dokumen_tahapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('tahapan_id')->constrained('master_tahapan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Pengunggah

            $table->text('nama_dokumen');
            $table->text('file_path'); // path file dalam storage
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable(); // bytes
            $table->string('file_type')->nullable(); // MIME type

            // Status verifikasi
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('catatan_verifikator')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Index
            $table->index(['permohonan_id', 'tahapan_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });

        // Detail verifikasi berdasarkan master_kelengkapan_verifikasi
        Schema::create('dokumen_verifikasi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_tahapan_id')->constrained('dokumen_tahapan')->cascadeOnDelete();
            $table->foreignId('master_kelengkapan_id')->constrained('master_kelengkapan_verifikasi')->cascadeOnDelete();

            $table->enum('status', ['lengkap', 'tidak_lengkap', 'revisi'])->default('tidak_lengkap');
            $table->text('catatan')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();

            // Index
            $table->index(['dokumen_tahapan_id']);
            $table->index(['master_kelengkapan_id']);

            // Unique: satu dokumen hanya punya 1 status per kelengkapan
            $table->unique(['dokumen_tahapan_id', 'master_kelengkapan_id'], 'dokumen_kelengkapan_unique');
        });

        // Upload ulang saat dokumen dikembalikan
        Schema::create('dokumen_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_tahapan_id')->constrained('dokumen_tahapan')->cascadeOnDelete();

            $table->text('file_path');
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->text('alasan_revisi')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            // Index
            $table->index(['dokumen_tahapan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_revisi');
        Schema::dropIfExists('dokumen_verifikasi_detail');
        Schema::dropIfExists('dokumen_tahapan');
    }
};
