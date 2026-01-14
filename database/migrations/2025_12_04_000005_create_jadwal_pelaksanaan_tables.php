<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign keys yang bergantung pada jadwal_fasilitasi
        if (Schema::hasTable('surat_pemberitahuan')) {
            Schema::table('surat_pemberitahuan', function (Blueprint $table) {
                if (Schema::hasColumn('surat_pemberitahuan', 'jadwal_fasilitasi_id')) {
                    $table->dropForeign(['jadwal_fasilitasi_id']);
                }
            });
        }

        if (Schema::hasTable('permohonan')) {
            Schema::table('permohonan', function (Blueprint $table) {
                if (Schema::hasColumn('permohonan', 'jadwal_fasilitasi_id')) {
                    $table->dropForeign(['jadwal_fasilitasi_id']);
                }
            });
        }

        // Refactor jadwal_fasilitasi menjadi tabel global untuk Admin Peran
        // Kab/Kota membuat permohonan berdasarkan jadwal ini
        Schema::dropIfExists('jadwal_fasilitasi');

        Schema::create('jadwal_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun_anggaran');
            $table->foreignId('jenis_dokumen')->constrained('master_jenis_dokumen')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('batas_permohonan')->nullable(); // batas waktu kab/kota buat permohonan
            $table->text('undangan_file')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['tahun_anggaran', 'jenis_dokumen']);
            $table->index(['status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index(['batas_permohonan']);
        });

        // Restore foreign keys setelah jadwal_fasilitasi dibuat ulang
        if (Schema::hasTable('permohonan')) {
            Schema::table('permohonan', function (Blueprint $table) {
                if (!Schema::hasColumn('permohonan', 'jadwal_fasilitasi_id')) {
                    $table->foreignId('jadwal_fasilitasi_id')->after('kab_kota_id')->constrained('jadwal_fasilitasi')->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasTable('surat_pemberitahuan')) {
            Schema::table('surat_pemberitahuan', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_pemberitahuan', 'jadwal_fasilitasi_id')) {
                    $table->foreignId('jadwal_fasilitasi_id')->after('id')->constrained('jadwal_fasilitasi')->cascadeOnDelete();
                }
            });
        }

        // Catatan pelaksanaan: berita acara, notulensi, foto kegiatan, absensi
        Schema::create('pelaksanaan_catatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();

            $table->text('berita_acara_file')->nullable();
            $table->text('notulensi_file')->nullable();
            $table->text('dokumentasi_file')->nullable(); // ZIP / folder
            $table->text('absensi_file')->nullable();
            $table->text('keterangan')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Index
            $table->index(['permohonan_id']);
        });

        // Dokumen hasil fasilitasi (draft & final)
        Schema::create('hasil_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();

            $table->text('draft_file')->nullable();
            $table->text('final_file')->nullable();
            $table->string('surat_penyampaian')->nullable();
            $table->foreignId('surat_dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('surat_tanggal')->nullable();
            $table->text('catatan')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index
            $table->index(['permohonan_id']);

            // Unique: satu permohonan hanya punya 1 hasil fasilitasi
            $table->unique(['permohonan_id']);
        });

        // Dokumen peraturan daerah/perkada final dari Kab/Kota
        Schema::create('penetapan_perda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();

            $table->string('nomor_perda', 100);
            $table->date('tanggal_penetapan');
            $table->text('file_perda');
            $table->text('keterangan')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Index
            $table->index(['permohonan_id']);
            $table->index(['tanggal_penetapan']);

            // Unique: satu permohonan hanya punya 1 penetapan
            $table->unique(['permohonan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penetapan_perda');
        Schema::dropIfExists('hasil_fasilitasi');
        Schema::dropIfExists('pelaksanaan_catatan');
        Schema::dropIfExists('jadwal_fasilitasi');

        // Restore jadwal_fasilitasi yang lama (global)
        Schema::create('jadwal_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->string('nama_kegiatan', 200);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('batas_permohonan')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun_anggaran_id', 'jenis_dokumen_id']);
            $table->index('status');
            $table->index('batas_permohonan');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });

        // Restore foreign key di surat_pemberitahuan
        if (Schema::hasTable('surat_pemberitahuan')) {
            Schema::table('surat_pemberitahuan', function (Blueprint $table) {
                $table->foreignId('jadwal_fasilitasi_id')->after('id')->constrained('jadwal_fasilitasi')->cascadeOnDelete();
            });
        }
    }
};
