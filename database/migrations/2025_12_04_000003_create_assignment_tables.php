<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penugasan koordinator untuk permohonan
        Schema::create('koordinator_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('koordinator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            // Index
            $table->index(['permohonan_id']);
            $table->index(['koordinator_id']);

            // Satu permohonan hanya bisa punya 1 koordinator aktif
            $table->unique(['permohonan_id']);
        });

        // Anggota tim fasilitasi yang menangani permohonan
        Schema::create('tim_fasilitasi_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            // Index
            $table->index(['permohonan_id']);
            $table->index(['user_id']);

            // Unique: satu user tidak bisa ditugaskan 2x di permohonan yang sama
            $table->unique(['permohonan_id', 'user_id']);
        });

        // Verifikator dokumen
        Schema::create('tim_verifikasi_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            // Index
            $table->index(['permohonan_id']);
            $table->index(['user_id']);

            // Unique constraint
            $table->unique(['permohonan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_verifikasi_assignment');
        Schema::dropIfExists('tim_fasilitasi_assignment');
        Schema::dropIfExists('koordinator_assignment');
    }
};
