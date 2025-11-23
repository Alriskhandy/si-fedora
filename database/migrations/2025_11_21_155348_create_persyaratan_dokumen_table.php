<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persyaratan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_dokumen_id');
            $table->string('kode', 20);
            $table->string('nama', 200);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_wajib')->default(true);
            $table->integer('urutan')->default(0);
            $table->string('template_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('persyaratan_dokumen', function (Blueprint $table) {
            $table->foreign('jenis_dokumen_id')->references('id')->on('jenis_dokumen')->cascadeOnDelete();
        });
        
        // Tambahin index buat performance
        Schema::table('persyaratan_dokumen', function (Blueprint $table) {
            $table->index(['jenis_dokumen_id', 'urutan']);
            $table->index('is_wajib');
            $table->unique(['jenis_dokumen_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persyaratan_dokumen');
    }
};