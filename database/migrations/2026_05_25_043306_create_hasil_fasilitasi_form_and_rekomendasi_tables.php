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
        Schema::create('hasil_fasilitasi_form', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_fasilitasi_id')->constrained('hasil_fasilitasi')->cascadeOnDelete();
            $table->text('catatan');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('hasil_fasilitasi_rekomendasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_fasilitasi_id')->constrained('hasil_fasilitasi')->cascadeOnDelete();
            $table->text('catatan');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_fasilitasi_rekomendasi');
        Schema::dropIfExists('hasil_fasilitasi_form');
    }
};
