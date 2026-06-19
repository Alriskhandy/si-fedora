<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dokumen_verifikasi_detail')) {
            Schema::create('dokumen_verifikasi_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dokumen_tahapan_id')->constrained('dokumen_tahapan')->cascadeOnDelete();
                $table->foreignId('master_kelengkapan_id')->constrained('master_kelengkapan_verifikasi')->cascadeOnDelete();

                $table->enum('status', ['lengkap', 'tidak_lengkap', 'revisi'])->default('tidak_lengkap');
                $table->text('catatan')->nullable();

                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('updated_at')->nullable();

                $table->index(['dokumen_tahapan_id']);
                $table->index(['master_kelengkapan_id']);
                $table->unique(['dokumen_tahapan_id', 'master_kelengkapan_id'], 'dokumen_kelengkapan_unique');
            });
        }

        if (!Schema::hasTable('dokumen_revisi')) {
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

                $table->index(['dokumen_tahapan_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('penetapan_perda')) {
            Schema::create('penetapan_perda', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();

                $table->string('nomor_perda', 100);
                $table->date('tanggal_penetapan');
                $table->text('file_perda');
                $table->text('keterangan')->nullable();

                $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['permohonan_id']);
                $table->index(['tanggal_penetapan']);
                $table->unique(['permohonan_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_verifikasi_detail');
        Schema::dropIfExists('dokumen_revisi');
        Schema::dropIfExists('penetapan_perda');
    }
};
