<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id');
            $table->foreignId('persyaratan_dokumen_id');
            
            $table->boolean('is_ada')->default(false);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            
            // Verifikasi
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected', 'revision'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->foreign('permohonan_id')->references('id')->on('permohonan')->cascadeOnDelete();
            $table->foreign('persyaratan_dokumen_id')->references('id')->on('persyaratan_dokumen')->cascadeOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
        
        // Tambahin unique constraint setelah foreign key dibuat
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->unique(['permohonan_id', 'persyaratan_dokumen_id'], 'permohonan_persyaratan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_dokumen');
    }
};