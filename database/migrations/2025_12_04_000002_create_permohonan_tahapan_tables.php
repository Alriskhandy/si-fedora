<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel tracking tahapan per permohonan
        Schema::create('permohonan_tahapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('tahapan_id')->constrained('master_tahapan')->cascadeOnDelete();

            $table->enum('status', ['belum', 'proses', 'revisi', 'selesai'])->default('belum');
            $table->text('catatan')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index untuk performa
            $table->index(['permohonan_id', 'tahapan_id']);
            $table->index(['status']);

            // Unique constraint: satu permohonan hanya punya 1 record per tahapan
            $table->unique(['permohonan_id', 'tahapan_id']);
        });

        // Audit trail untuk perubahan status tahapan
        Schema::create('permohonan_tahapan_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_tahapan_id')->constrained('permohonan_tahapan')->cascadeOnDelete();

            $table->enum('status_lama', ['belum', 'proses', 'revisi', 'selesai'])->nullable();
            $table->enum('status_baru', ['belum', 'proses', 'revisi', 'selesai']);
            $table->text('keterangan')->nullable();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            // Index untuk performa
            $table->index(['permohonan_tahapan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_tahapan_log');
        Schema::dropIfExists('permohonan_tahapan');
    }
};
