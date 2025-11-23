<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tim_pokja', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->foreignId('ketua_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('pokja_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokja_id');
            $table->foreignId('user_id');
            $table->string('jabatan')->nullable();
            $table->timestamps();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('tim_pokja', function (Blueprint $table) {
            $table->foreign('ketua_id')->references('id')->on('users')->nullOnDelete();
        });
        
        Schema::table('pokja_anggota', function (Blueprint $table) {
            $table->foreign('pokja_id')->references('id')->on('tim_pokja')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        // Tambahin index buat performance
        Schema::table('pokja_anggota', function (Blueprint $table) {
            $table->unique(['pokja_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokja_anggota');
        Schema::dropIfExists('tim_pokja');
    }
};