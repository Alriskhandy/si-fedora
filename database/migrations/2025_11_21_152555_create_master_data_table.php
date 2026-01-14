<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_tahapan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tahapan');
            $table->integer('urutan')->unique();
            $table->timestamps();
        });

        Schema::create('master_jenis_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('master_bab', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bab');
            $table->foreignId('jenis_dokumen_id')->nullable()->constrained('master_jenis_dokumen')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('master_bab')->nullOnDelete();
            $table->integer('urutan')->nullable();
            $table->timestamps();
        });

        Schema::create('master_urusan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_urusan');
            $table->enum('kategori', [
                'wajib_dasar',
                'wajib_non_dasar',
                'pilihan'
            ]);
            $table->integer('urutan')->nullable();
            $table->timestamps();
        });

        Schema::create('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dokumen');
            $table->enum('kategori', ['surat_permohonan', 'kelengkapan_verifikasi'])->default('kelengkapan_verifikasi');
            $table->foreignId('tahapan_id')->nullable()->constrained('master_tahapan')->nullOnDelete()->comment('Kelengkapan untuk tahapan tertentu (null = berlaku untuk semua)');
            $table->text('deskripsi')->nullable();
            $table->boolean('wajib')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();

            // Index
            $table->index(['kategori', 'tahapan_id']);
            $table->index(['wajib', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kelengkapan_verifikasi');
        Schema::dropIfExists('master_urusan');
        Schema::dropIfExists('master_bab');
        Schema::dropIfExists('master_jenis_dokumen');
        Schema::dropIfExists('master_tahapan');
    }
};
