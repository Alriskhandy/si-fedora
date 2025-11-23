<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permohonan', 50)->unique();
            $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('jadwal_fasilitasi_id')->nullable()->constrained('jadwal_fasilitasi')->nullOnDelete();
            
            $table->string('nama_dokumen', 200);
            $table->text('keterangan')->nullable();
            $table->date('tanggal_permohonan');
            
            // Status workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'verified',
                'revision_required',
                'assigned',
                'in_evaluation',
                'draft_recommendation',
                'approved_by_kaban',
                'letter_issued',
                'sent',
                'follow_up',
                'completed',
                'rejected'
            ])->default('draft');
            
            // Tracking
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Assignment
            $table->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pokja_id')->nullable()->constrained('tim_pokja')->nullOnDelete();
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'kabupaten_kota_id']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};