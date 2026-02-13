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
            $table->string('draft_final_file')->nullable()->after('draft_file');
            $table->string('status_draft')->nullable()->after('draft_final_file');
            $table->timestamp('tanggal_diajukan_kaban')->nullable()->after('status_draft');
            $table->timestamp('tanggal_disetujui_kaban')->nullable()->after('tanggal_diajukan_kaban');
            $table->text('keterangan_kaban')->nullable()->after('tanggal_disetujui_kaban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_fasilitasi', function (Blueprint $table) {
            $table->dropColumn(['draft_final_file', 'status_draft', 'tanggal_diajukan_kaban', 'tanggal_disetujui_kaban', 'keterangan_kaban']);
        });
    }
};
