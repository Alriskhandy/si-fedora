<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel untuk revisi dokumen (tracking history)
        Schema::create('dokumen_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            
            $table->text('file_path');
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type', 100)->nullable();
            $table->text('alasan_revisi')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            // Index
            $table->index(['dokumen_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_revisi');
    }
};
