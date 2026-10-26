<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda "perkiraan" tidak dipakai: tanggal selesai diisi sistem dan dapat diubah Admin.
     */
    public function up(): void
    {
        Schema::table('permohonan_tahapan', function (Blueprint $table) {
            $table->dropColumn('tanggal_selesai_perkiraan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_tahapan', function (Blueprint $table) {
            $table->boolean('tanggal_selesai_perkiraan')->default(false)->after('tanggal_selesai');
        });
    }
};
