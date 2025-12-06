<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign keys yang bergantung pada permohonan
        Schema::table('permohonan', function (Blueprint $table) {
            // Check if foreign keys exist before dropping
            if (Schema::hasColumn('permohonan', 'jadwal_fasilitasi_id')) {
                $table->dropForeign(['jadwal_fasilitasi_id']);
            }
            if (Schema::hasColumn('permohonan', 'verifikator_id')) {
                $table->dropForeign(['verifikator_id']);
            }
            if (Schema::hasColumn('permohonan', 'pokja_id')) {
                $table->dropForeign(['pokja_id']);
            }
            if (Schema::hasColumn('permohonan', 'jenis_dokumen_id')) {
                $table->dropForeign(['jenis_dokumen_id']);
            }
        });

        // Simplifikasi tabel permohonan sesuai desain baru
        Schema::table('permohonan', function (Blueprint $table) {
            // Drop kolom yang tidak diperlukan (workflow dipindah ke permohonan_tahapan)
            $columns = [
                'nomor_permohonan',
                'nama_dokumen',
                'keterangan',
                'tanggal_permohonan',
                'tahun_anggaran_id',
                'status',
                'submitted_at',
                'verified_at',
                'assigned_at',
                'evaluated_at',
                'approved_at',
                'completed_at',
                'jadwal_fasilitasi_id',
                'verifikator_id',
                'pokja_id',
                'jenis_dokumen_id'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('permohonan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Tambah kolom sesuai desain baru
        Schema::table('permohonan', function (Blueprint $table) {
            // Ubah jenis_dokumen_id menjadi enum langsung
            if (!Schema::hasColumn('permohonan', 'tahun')) {
                $table->integer('tahun')->after('kabupaten_kota_id');
            }
            if (!Schema::hasColumn('permohonan', 'jenis_dokumen')) {
                $table->enum('jenis_dokumen', ['rkpd', 'rpd', 'rpjmd'])->after('tahun');
            }

            // Status akhir proses
            if (!Schema::hasColumn('permohonan', 'status_akhir')) {
                $table->enum('status_akhir', ['belum', 'proses', 'revisi', 'selesai'])->default('belum')->after('jenis_dokumen');
            }

            // Note: jadwal_fasilitasi_id akan ditambahkan di migration 000005 setelah tabel jadwal_fasilitasi dibuat ulang
        });

        // Rename kab_kota untuk konsistensi
        if (Schema::hasColumn('permohonan', 'kabupaten_kota_id') && !Schema::hasColumn('permohonan', 'kab_kota_id')) {
            Schema::table('permohonan', function (Blueprint $table) {
                $table->renameColumn('kabupaten_kota_id', 'kab_kota_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('permohonan', function (Blueprint $table) {
            // Restore original columns
            $table->renameColumn('kab_kota_id', 'kabupaten_kota_id');

            $table->foreignId('jenis_dokumen_id')->after('kabupaten_kota_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->foreignId('jadwal_fasilitasi_id')->nullable()->constrained('jadwal_fasilitasi')->nullOnDelete();

            $table->string('nomor_permohonan', 50)->unique();
            $table->string('nama_dokumen', 200);
            $table->text('keterangan')->nullable();
            $table->date('tanggal_permohonan');

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

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pokja_id')->nullable()->constrained('tim_pokja')->nullOnDelete();

            $table->dropColumn(['tahun', 'jenis_dokumen', 'status_akhir']);
        });
    }
};
