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
        Schema::create('user_kabkota_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->onDelete('cascade');
            $table->foreignId('jenis_dokumen_id')->nullable()->constrained('master_jenis_dokumen')->onDelete('set null');
            $table->enum('role_type', ['verifikator', 'fasilitator', 'koordinator']);
            $table->boolean('is_pic')->default(false)->comment('PIC/Ketua Tim untuk kabupaten/kota ini');
            $table->year('tahun')->comment('Tahun penugasan');
            $table->string('nomor_surat')->nullable()->comment('Nomor surat penugasan');
            $table->string('file_sk')->nullable()->comment('File SK Tim');
            $table->date('assigned_from')->nullable()->comment('Mulai penugasan');
            $table->date('assigned_until')->nullable()->comment('Akhir penugasan');
            $table->boolean('is_active')->default(true)->comment('Status aktif assignment');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index(['user_id', 'kabupaten_kota_id', 'is_active'], 'idx_user_kabkota_active');
            $table->index(['kabupaten_kota_id', 'role_type', 'is_active'], 'idx_kabkota_role_active');
            $table->index(['user_id', 'role_type', 'is_active'], 'idx_user_role_active');
            $table->index(['jenis_dokumen_id', 'tahun'], 'idx_jenis_tahun');
            $table->index('is_pic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_kabkota_assignments');
    }
};
