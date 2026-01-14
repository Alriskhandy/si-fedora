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
        // Tabel untuk menyimpan catatan sistematika per Bab/Sub Bab
        if (!Schema::hasTable('hasil_fasilitasi_sistematika')) {
            Schema::create('hasil_fasilitasi_sistematika', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hasil_fasilitasi_id')->constrained('hasil_fasilitasi')->cascadeOnDelete();
                $table->string('bab_sub_bab');
                $table->text('catatan_penyempurnaan');
                $table->timestamps();
            });
        }

        // Tabel untuk menyimpan catatan per urusan pemerintahan
        if (!Schema::hasTable('hasil_fasilitasi_urusan')) {
            Schema::create('hasil_fasilitasi_urusan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hasil_fasilitasi_id')->constrained('hasil_fasilitasi')->cascadeOnDelete();
                $table->foreignId('master_urusan_id')->constrained('master_urusan')->cascadeOnDelete();
                $table->text('catatan_masukan');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_fasilitasi_urusan');
        Schema::dropIfExists('hasil_fasilitasi_sistematika');
    }
};
