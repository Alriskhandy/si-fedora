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
        Schema::table('perpanjangan_waktu', function (Blueprint $table) {
            $table->foreignId('jadwal_fasilitasi_id')->nullable()->after('permohonan_id')
                ->constrained('jadwal_fasilitasi')->onDelete('cascade');
        });

        Schema::table('perpanjangan_waktu', function (Blueprint $table) {
            $table->foreignId('permohonan_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perpanjangan_waktu', function (Blueprint $table) {
            $table->dropForeign(['jadwal_fasilitasi_id']);
            $table->dropColumn('jadwal_fasilitasi_id');
        });
    }
};
