<?php

/**
 * ============================================
 * LARAVEL MIGRATIONS FOR SI-FEDORA
 * Database: PostgreSQL
 * ============================================
 * 
 * Urutan eksekusi migrations (sesuai timestamp):
 * 1. users, kabupaten_kota, tahun_anggaran, jenis_dokumen, tim_pokja
 * 2. persyaratan_dokumen, anggota_pokja, jadwal_fasilitasi
 * 3. surat_pemberitahuan, permohonan_fasilitasi
 * 4. dokumen_permohonan, verifikasi_permohonan
 * 5. penugasan_evaluasi, pelaksanaan_fasilitasi, hasil_evaluasi
 * 6. persetujuan_kaban, surat_rekomendasi, lampiran_surat_rekomendasi
 * 7. tindak_lanjut, penetapan_dokumen
 * 8. notifikasi, log_aktivitas, pengaturan_sistem
 */

// ============================================
// 2024_01_01_000001_create_users_table.php
// ============================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['superadmin', 'kaban', 'admin_peran', 'tim_verifikasi', 'tim_evaluasi', 'kabkota']);
            $table->foreignId('kabkota_id')->nullable()->constrained('kabupaten_kota')->nullOnDelete();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('role');
            $table->index('kabkota_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

// ============================================
// 2024_01_01_000002_create_kabupaten_kota_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kabupaten_kota', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->enum('jenis', ['kabupaten', 'kota']);
            $table->string('nama_bupati_walikota')->nullable();
            $table->string('nama_kepala_bappeda')->nullable();
            $table->text('alamat')->nullable();
            $table->string('email')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabupaten_kota');
    }
};

// ============================================
// 2024_01_01_000003_create_jenis_dokumen_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('periode', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data awal
        DB::table('jenis_dokumen')->insert([
            [
                'kode' => 'RKPD',
                'nama' => 'Rencana Kerja Pemerintah Daerah',
                'periode' => '1 tahun (2x setahun)',
                'deskripsi' => 'Dokumen perencanaan tahunan daerah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'RPJMD',
                'nama' => 'Rencana Pembangunan Jangka Menengah Daerah',
                'periode' => '5 tahun',
                'deskripsi' => 'Dokumen perencanaan jangka menengah daerah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'RPD',
                'nama' => 'Rencana Pembangunan Daerah',
                'periode' => 'Jangka Panjang',
                'deskripsi' => 'Dokumen perencanaan jangka panjang pada masa transisi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_dokumen');
    }
};

// ============================================
// 2024_01_01_000004_create_tahun_anggaran_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_anggaran', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_anggaran');
    }
};

// ============================================
// 2024_01_01_000005_create_tim_pokja_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tim_pokja', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode', 50)->unique()->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_pokja');
    }
};

// ============================================
// 2024_01_01_000006_create_persyaratan_dokumen_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persyaratan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->integer('nomor_urut');
            $table->string('nama_persyaratan');
            $table->string('kode', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('tipe_file', 100)->nullable()->comment('PDF, Excel, Word');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('jenis_dokumen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persyaratan_dokumen');
    }
};

// ============================================
// 2024_01_01_000007_create_anggota_pokja_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_pokja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tim_pokja_id')->constrained('tim_pokja')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jabatan', 100)->nullable()->comment('Ketua, Anggota, Sekretaris');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('tim_pokja_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_pokja');
    }
};

// ============================================
// 2024_01_01_000008_create_jadwal_fasilitasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->cascadeOnDelete();
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->string('nomor_surat', 100)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->date('periode_mulai')->comment('Rentang waktu pelaksanaan');
            $table->date('periode_selesai');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('tahun_anggaran_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_fasilitasi');
    }
};

// ============================================
// 2024_01_01_000009_create_surat_pemberitahuan_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pemberitahuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_fasilitasi_id')->constrained('jadwal_fasilitasi')->cascadeOnDelete();
            $table->foreignId('kabkota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $table->string('nomor_surat', 100);
            $table->date('tanggal_surat');
            $table->text('perihal')->nullable();
            $table->string('file_surat')->nullable();
            $table->enum('status_pengiriman', ['draft', 'sent', 'received'])->default('draft');
            $table->timestamp('tanggal_kirim')->nullable();
            $table->timestamp('tanggal_terima')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('jadwal_fasilitasi_id');
            $table->index('kabkota_id');
            $table->index('status_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_pemberitahuan');
    }
};

// ============================================
// 2024_01_01_000010_create_permohonan_fasilitasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_fasilitasi_id')->constrained('jadwal_fasilitasi')->cascadeOnDelete();
            $table->foreignId('kabkota_id')->constrained('kabupaten_kota')->cascadeOnDelete();
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->string('nomor_surat', 100);
            $table->date('tanggal_surat');
            $table->text('perihal')->nullable();
            $table->string('file_surat_permohonan')->nullable();
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected', 'approved', 'completed'])->default('draft');
            $table->timestamp('tanggal_submit')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('jadwal_fasilitasi_id');
            $table->index('kabkota_id');
            $table->index('status');
            $table->index(['status', 'kabkota_id']);
            $table->index(['jadwal_fasilitasi_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_fasilitasi');
    }
};

// ============================================
// 2024_01_01_000011_create_dokumen_permohonan_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_permohonan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->foreignId('persyaratan_dokumen_id')->constrained('persyaratan_dokumen')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('status_verifikasi');
            $table->index(['permohonan_fasilitasi_id', 'status_verifikasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_permohonan');
    }
};

// ============================================
// 2024_01_01_000012_create_verifikasi_permohonan_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_permohonan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->enum('hasil_verifikasi', ['lengkap', 'tidak_lengkap', 'perlu_perbaikan']);
            $table->text('catatan')->nullable();
            $table->string('file_hasil_verifikasi')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->foreignId('verified_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->comment('Admin PERAN')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_permohonan');
    }
};

// ============================================
// 2024_01_01_000013_create_penugasan_evaluasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penugasan_evaluasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->foreignId('tim_pokja_id')->constrained('tim_pokja')->cascadeOnDelete();
            $table->date('tanggal_penugasan');
            $table->date('batas_waktu_evaluasi')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'revised'])->default('assigned');
            $table->text('keterangan')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('tim_pokja_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_evaluasi');
    }
};

// ============================================
// 2024_01_01_000014_create_pelaksanaan_fasilitasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelaksanaan_fasilitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->string('nomor_undangan', 100)->nullable();
            $table->date('tanggal_undangan')->nullable();
            $table->string('file_undangan')->nullable();
            $table->timestamp('tanggal_pelaksanaan');
            $table->string('tempat')->nullable();
            $table->text('agenda')->nullable();
            $table->string('file_absensi')->nullable();
            $table->string('file_notulensi')->nullable();
            $table->string('file_berita_acara')->nullable();
            $table->string('file_dokumentasi')->nullable();
            $table->string('file_materi_presentasi')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('tanggal_pelaksanaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelaksanaan_fasilitasi');
    }
};

// ============================================
// 2024_01_01_000015_create_hasil_evaluasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_evaluasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penugasan_evaluasi_id')->constrained('penugasan_evaluasi')->cascadeOnDelete();
            $table->text('draft_rekomendasi')->nullable();
            $table->string('file_draft_rekomendasi')->nullable();
            $table->text('catatan_evaluasi')->nullable();
            $table->string('file_catatan')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'revised'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('penugasan_evaluasi_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_evaluasi');
    }
};

// ============================================
// 2024_01_01_000016_create_persetujuan_kaban_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persetujuan_kaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_evaluasi_id')->constrained('hasil_evaluasi')->cascadeOnDelete();
            $table->enum('status_persetujuan', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_persetujuan')->nullable();
            $table->foreignId('approved_by')->nullable()->comment('Kaban')->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('hasil_evaluasi_id');
            $table->index('status_persetujuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_kaban');
    }
};

// ============================================
// 2024_01_01_000017_create_surat_rekomendasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_rekomendasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->foreignId('hasil_evaluasi_id')->constrained('hasil_evaluasi')->cascadeOnDelete();
            $table->string('nomor_surat', 100);
            $table->date('tanggal_surat');
            $table->text('perihal')->nullable();
            $table->text('isi_rekomendasi')->nullable();
            $table->string('file_surat')->nullable();
            $table->enum('status', ['draft', 'signed', 'sent', 'received'])->default('draft');
            $table->timestamp('tanggal_ttd')->nullable();
            $table->foreignId('signed_by')->nullable()->comment('Kaban')->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_kirim')->nullable();
            $table->timestamp('tanggal_terima')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_rekomendasi');
    }
};

// ============================================
// 2024_01_01_000018_create_lampiran_surat_rekomendasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran_surat_rekomendasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_rekomendasi_id')->constrained('surat_rekomendasi')->cascadeOnDelete();
            $table->string('nama_lampiran');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('surat_rekomendasi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_surat_rekomendasi');
    }
};

// ============================================
// 2024_01_01_000019_create_tindak_lanjut_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->foreignId('surat_rekomendasi_id')->constrained('surat_rekomendasi')->cascadeOnDelete();
            $table->string('nomor_surat', 100)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->text('uraian_tindak_lanjut')->nullable();
            $table->string('file_laporan')->nullable();
            $table->enum('status', ['pending', 'submitted', 'completed'])->default('pending');
            $table->timestamp('tanggal_submit')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};

// ============================================
// 2024_01_01_000020_create_penetapan_dokumen_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penetapan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_fasilitasi_id')->constrained('permohonan_fasilitasi')->cascadeOnDelete();
            $table->foreignId('tindak_lanjut_id')->constrained('tindak_lanjut')->cascadeOnDelete();
            $table->enum('jenis_penetapan', ['perda', 'perkada']);
            $table->string('nomor_penetapan', 100);
            $table->date('tanggal_penetapan');
            $table->string('nomor_registrasi', 100)->nullable();
            $table->string('file_penetapan')->nullable();
            $table->enum('status', ['draft', 'registered', 'completed'])->default('draft');
            $table->date('tanggal_registrasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('permohonan_fasilitasi_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penetapan_dokumen');
    }
};

// ============================================
// 2024_01_01_000021_create_notifikasi_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('kabkota_id')->nullable()->constrained('kabupaten_kota')->cascadeOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['email', 'whatsapp', 'system']);
            $table->string('reference_type', 100)->nullable()->comment('Model class name');
            $table->bigInteger('reference_id')->nullable()->comment('Model ID');
            $table->enum('status', ['pending', 'sent', 'failed', 'read'])->default('pending');
            $table->timestamp('tanggal_kirim')->nullable();
            $table->timestamp('tanggal_baca')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('kabkota_id');
            $table->index('status');
            $table->index('tipe');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};

// ============================================
// 2024_01_01_000022_create_log_aktivitas_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('activity_type', 100);
            $table->text('description')->nullable();
            $table->string('model_type', 100)->nullable();
            $table->bigInteger('model_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index('activity_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};

// ============================================
// 2024_01_01_000023_create_pengaturan_sistem_table.php
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique();
            $table->text('key_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }
};

// ============================================
// 2024_01_01_000024_add_foreign_key_to_users_table.php
// ============================================
return new class extends Migration
{
    /**
     * Migration ini ditambahkan setelah kabupaten_kota table dibuat
     * untuk menambahkan foreign key constraint
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kabkota_id')
                  ->references('id')
                  ->on('kabupaten_kota')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kabkota_id']);
        });
    }
};