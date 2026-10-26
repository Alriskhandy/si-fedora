<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat perubahan tanggal selesai dan batas waktu setiap tahapan permohonan (PRD #5).
     */
    public function up(): void
    {
        Schema::create('permohonan_tahapan_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_tahapan_id')->constrained('permohonan_tahapan')->cascadeOnDelete();
            $table->string('jenis', 20);
            $table->timestamp('nilai_lama')->nullable();
            $table->timestamp('nilai_baru')->nullable();
            $table->text('alasan');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['permohonan_tahapan_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_tahapan_riwayat');
    }
};
