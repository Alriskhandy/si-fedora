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
        Schema::dropIfExists('fasilitasi_bab');
        Schema::dropIfExists('fasilitasi_urusan');
        Schema::dropIfExists('koordinator_assignment');
        Schema::dropIfExists('tim_verifikasi_assignment');
        Schema::dropIfExists('permohonan_tahapan_log');
        Schema::dropIfExists('dokumen_revisi');
        Schema::dropIfExists('dokumen_verifikasi_detail');
        Schema::dropIfExists('penetapan_perda');
        Schema::dropIfExists('surat_rekomendasi');
        Schema::dropIfExists('rich_texts');
        Schema::dropIfExists('whatsapp_log');
        Schema::dropIfExists('temporary_role_assignments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('fasilitasi_bab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('bab_id')->constrained('master_bab')->cascadeOnDelete();

            $table->text('catatan')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['permohonan_id', 'bab_id']);
            $table->unique(['permohonan_id', 'bab_id']);
        });

        Schema::create('fasilitasi_urusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('urusan_id')->constrained('master_urusan')->cascadeOnDelete();

            $table->text('kondisi_umum')->nullable();
            $table->text('permasalahan')->nullable();
            $table->text('analisis_kinerja')->nullable();
            $table->text('kesesuaian_dokumen')->nullable();
            $table->text('rekomendasi')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['permohonan_id', 'urusan_id']);
            $table->unique(['permohonan_id', 'urusan_id']);
        });

        Schema::create('koordinator_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('koordinator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index(['permohonan_id']);
            $table->index(['koordinator_id']);
            $table->unique(['permohonan_id']);
        });

        Schema::create('tim_verifikasi_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index(['permohonan_id']);
            $table->index(['user_id']);
            $table->unique(['permohonan_id', 'user_id']);
        });

        Schema::create('permohonan_tahapan_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_tahapan_id')->constrained('permohonan_tahapan')->cascadeOnDelete();

            $table->enum('status_lama', ['belum', 'proses', 'revisi', 'selesai'])->nullable();
            $table->enum('status_baru', ['belum', 'proses', 'revisi', 'selesai']);
            $table->text('keterangan')->nullable();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index(['permohonan_tahapan_id', 'created_at']);
        });

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

        Schema::create('surat_rekomendasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id');

            $table->string('nomor_surat', 50)->unique();
            $table->date('tanggal_surat');
            $table->string('perihal', 200);
            $table->text('isi_surat')->nullable();

            $table->string('file_surat')->nullable();
            $table->string('file_ttd')->nullable();
            $table->string('file_lampiran')->nullable();

            $table->enum('status', ['draft', 'approved', 'signed', 'sent'])->default('draft');

            $table->foreignId('signed_by')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('surat_rekomendasi', function (Blueprint $table) {
            $table->foreign('permohonan_id')->references('id')->on('permohonan')->cascadeOnDelete();
            $table->foreign('signed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('rich_texts', function (Blueprint $table) {
            $table->id();
            $table->morphs('record');
            $table->string('field');
            $table->longText('body')->nullable();
            $table->timestamps();

            $table->unique(['field', 'record_type', 'record_id']);
        });

        Schema::create('whatsapp_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('phone_number', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::table('whatsapp_log', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('phone_number');
            $table->index('status');
            $table->index('sent_at');
        });

        Schema::create('temporary_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('delegated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
