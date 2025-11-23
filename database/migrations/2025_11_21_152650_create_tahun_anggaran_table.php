<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_anggaran', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tambahin index buat performance
        Schema::table('tahun_anggaran', function (Blueprint $table) {
            $table->index('tahun');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_anggaran');
    }
};