<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pemberitahuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_fasilitasi_id');
            $table->foreignId('kabupaten_kota_id');
            $table->string('nomor_surat', 50);
            $table->date('tanggal_surat');
            $table->string('perihal', 200);
            $table->text('isi_surat')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'sent', 'received'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('surat_pemberitahuan', function (Blueprint $table) {
            $table->foreign('jadwal_fasilitasi_id')->references('id')->on('jadwal_fasilitasi')->cascadeOnDelete();
            $table->foreign('kabupaten_kota_id')->references('id')->on('kabupaten_kota')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
        
        // Tambahin index buat performance
        Schema::table('surat_pemberitahuan', function (Blueprint $table) {
            $table->index(['jadwal_fasilitasi_id', 'kabupaten_kota_id']);
            $table->index('status');
            $table->index('sent_at');
            $table->unique('nomor_surat');  // Pastiin nomor surat unik
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_pemberitahuan');
    }
};