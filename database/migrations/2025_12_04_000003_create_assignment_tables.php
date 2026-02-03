<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel assignment terpadu untuk semua role (koordinator, verifikator, fasilitator)
        Schema::create('permohonan_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['koordinator', 'verifikator', 'fasilitator']);
            $table->boolean('is_pic')->default(false)->comment('Person in Charge / Ketua Tim');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index untuk performa
            $table->index(['permohonan_id', 'role']);
            $table->index(['user_id', 'role']);

            // Unique: satu user tidak bisa ditugaskan 2x dengan role yang sama di permohonan yang sama
            $table->unique(['permohonan_id', 'user_id', 'role'], 'permohonan_user_role_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_assignments');
    }
};
