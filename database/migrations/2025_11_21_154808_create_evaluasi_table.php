<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id');
            $table->foreignId('pokja_id');
            $table->foreignId('evaluator_id');
            
            $table->text('draft_rekomendasi')->nullable();
            $table->string('file_draft')->nullable();
            $table->text('catatan_evaluasi')->nullable();
            
            $table->enum('status', ['assigned', 'in_progress', 'submitted', 'revision', 'approved'])->default('assigned');
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Feedback dari Kaban
            $table->text('feedback_kaban')->nullable();
            $table->foreignId('approved_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('evaluasi', function (Blueprint $table) {
            $table->foreign('permohonan_id')->references('id')->on('permohonan')->cascadeOnDelete();
            $table->foreign('pokja_id')->references('id')->on('tim_pokja')->cascadeOnDelete();
            $table->foreign('evaluator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi');
    }
};