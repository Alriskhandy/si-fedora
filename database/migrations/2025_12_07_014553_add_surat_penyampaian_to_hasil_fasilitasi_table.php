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
        Schema::table('hasil_fasilitasi', function (Blueprint $table) {
            $table->string('surat_penyampaian')->nullable()->after('final_file');
            $table->foreignId('surat_dibuat_oleh')->nullable()->after('surat_penyampaian')->constrained('users')->nullOnDelete();
            $table->timestamp('surat_tanggal')->nullable()->after('surat_dibuat_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_fasilitasi', function (Blueprint $table) {
            $table->dropForeign(['surat_dibuat_oleh']);
            $table->dropColumn(['surat_penyampaian', 'surat_dibuat_oleh', 'surat_tanggal']);
        });
    }
};
