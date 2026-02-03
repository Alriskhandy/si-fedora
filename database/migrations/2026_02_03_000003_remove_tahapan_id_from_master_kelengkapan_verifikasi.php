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
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->dropForeign(['tahapan_id']);
            $table->dropColumn('tahapan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kelengkapan_verifikasi', function (Blueprint $table) {
            $table->unsignedBigInteger('tahapan_id')->nullable()->after('nama_dokumen');
            $table->foreign('tahapan_id')->references('id')->on('master_tahapan')->onDelete('set null');
        });
    }
};
