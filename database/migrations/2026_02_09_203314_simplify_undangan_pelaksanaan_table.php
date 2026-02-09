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
        Schema::table('undangan_pelaksanaan', function (Blueprint $table) {
            // Hapus kolom yang tidak digunakan lagi
            $table->dropColumn([
                'nomor_undangan',
                'perihal',
                'isi_undangan',
                'tanggal_dibuat',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('undangan_pelaksanaan', function (Blueprint $table) {
            // Kembalikan kolom jika rollback
            $table->string('nomor_undangan')->unique();
            $table->text('perihal');
            $table->text('isi_undangan')->nullable();
            $table->timestamp('tanggal_dibuat')->nullable();
        });
    }
};
