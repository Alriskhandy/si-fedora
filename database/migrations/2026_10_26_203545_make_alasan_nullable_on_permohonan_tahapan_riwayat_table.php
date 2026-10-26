<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perubahan tanggal selesai oleh Admin tidak lagi memakai alasan; alasan hanya untuk perubahan batas waktu.
     */
    public function up(): void
    {
        Schema::table('permohonan_tahapan_riwayat', function (Blueprint $table) {
            $table->text('alasan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_tahapan_riwayat', function (Blueprint $table) {
            $table->text('alasan')->nullable(false)->change();
        });
    }
};
