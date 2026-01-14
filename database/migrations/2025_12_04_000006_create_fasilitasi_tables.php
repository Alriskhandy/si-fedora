<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Masukan Tim Fasilitasi berdasarkan struktur bab dokumen
        Schema::create('fasilitasi_bab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('bab_id')->constrained('master_bab')->cascadeOnDelete();

            $table->text('catatan')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index
            $table->index(['permohonan_id', 'bab_id']);

            // Unique: satu permohonan hanya punya 1 catatan per bab
            $table->unique(['permohonan_id', 'bab_id']);
        });

        // Masukan terhadap 32 Urusan Pemerintahan
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

            // Index
            $table->index(['permohonan_id', 'urusan_id']);

            // Unique: satu permohonan hanya punya 1 evaluasi per urusan
            $table->unique(['permohonan_id', 'urusan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitasi_urusan');
        Schema::dropIfExists('fasilitasi_bab');
    }
};
