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
        // Tabel gabungan untuk menyimpan catatan sistematika (bab/sub-bab) dan urusan
        Schema::create('hasil_fasilitasi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_fasilitasi_id')->constrained('hasil_fasilitasi')->cascadeOnDelete();
            
            // Tipe detail: bab atau urusan
            $table->enum('tipe', ['bab', 'urusan']);
            
            // Untuk tipe 'bab'
            $table->foreignId('master_bab_id')->nullable()->constrained('master_bab')->nullOnDelete();
            $table->string('sub_bab')->nullable()->comment('Sub bab jika ada');
            
            // Untuk tipe 'urusan'
            $table->foreignId('master_urusan_id')->nullable()->constrained('master_urusan')->nullOnDelete();
            
            // Catatan/masukan
            $table->text('catatan')->comment('Catatan penyempurnaan untuk bab atau masukan untuk urusan');
            
            // Tracking
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['hasil_fasilitasi_id', 'tipe']);
            $table->index(['master_bab_id']);
            $table->index(['master_urusan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_fasilitasi_detail');
    }
};
