<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('tahapan_id')->nullable()->constrained('master_tahapan')->nullOnDelete();
            $table->foreignId('kelengkapan_id')->nullable()->constrained('master_kelengkapan_verifikasi')->nullOnDelete();
            
            // Kategori dokumen
            $table->enum('kategori', ['permohonan', 'verifikasi', 'pelaksanaan', 'hasil'])->default('permohonan');
            
            // Informasi dokumen
            $table->string('nama_dokumen');
            $table->text('file_path');
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable()->comment('Ukuran file dalam bytes');
            $table->string('file_type', 100)->nullable()->comment('MIME type');
            
            // Status verifikasi
            $table->enum('status', ['pending', 'verified', 'rejected', 'revision'])->default('pending');
            $table->text('catatan')->nullable();
            
            // Tracking
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['permohonan_id', 'kategori']);
            $table->index(['permohonan_id', 'tahapan_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
