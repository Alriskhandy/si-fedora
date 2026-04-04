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
        Schema::table('penetapan_jadwal_fasilitasi', function (Blueprint $table) {
            $table->foreignId('diubah_oleh')->nullable()->after('tanggal_penetapan')->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_perubahan')->nullable()->after('diubah_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penetapan_jadwal_fasilitasi', function (Blueprint $table) {
            $table->dropForeign(['diubah_oleh']);
            $table->dropColumn(['diubah_oleh', 'tanggal_perubahan']);
        });
    }
};
