<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('undangan_pelaksanaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('penetapan_jadwal_id')->constrained('penetapan_jadwal_fasilitasi')->cascadeOnDelete();
            $table->string('nomor_undangan')->unique();
            $table->text('perihal');
            $table->text('isi_undangan')->nullable();
            $table->string('file_undangan')->nullable();
            $table->enum('status', ['draft', 'terkirim'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_dibuat')->nullable();
            $table->timestamp('tanggal_dikirim')->nullable();
            $table->timestamps();
        });

        Schema::create('undangan_penerima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('undangan_id')->constrained('undangan_pelaksanaan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis_penerima', ['verifikator', 'fasilitator', 'pemohon'])->default('pemohon');
            $table->boolean('dibaca')->default(false);
            $table->timestamp('tanggal_dibaca')->nullable();
            $table->timestamps();

            $table->unique(['undangan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('undangan_penerima');
        Schema::dropIfExists('undangan_pelaksanaan');
    }
};
