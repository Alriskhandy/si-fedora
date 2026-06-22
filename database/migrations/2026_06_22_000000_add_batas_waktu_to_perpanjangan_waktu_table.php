<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perpanjangan_waktu', function (Blueprint $table) {
            $table->datetime('batas_waktu')->nullable()->after('surat_permohonan');
        });
    }

    public function down(): void
    {
        Schema::table('perpanjangan_waktu', function (Blueprint $table) {
            $table->dropColumn('batas_waktu');
        });
    }
};
