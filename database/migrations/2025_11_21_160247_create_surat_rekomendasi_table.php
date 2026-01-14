<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('surat_rekomendasi', function (Blueprint $table) {
            $table->foreign('permohonan_id')->references('id')->on('permohonan')->cascadeOnDelete();
            $table->foreign('signed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_rekomendasi');
    }
};