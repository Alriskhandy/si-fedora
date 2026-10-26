<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tanggal selesai tahapan (PRD #5). Data lama diisi dari updated_at dan ditandai sebagai perkiraan.
     */
    public function up(): void
    {
        Schema::table('permohonan_tahapan', function (Blueprint $table) {
            $table->timestamp('tanggal_selesai')->nullable()->after('deadline');
            $table->boolean('tanggal_selesai_perkiraan')->default(false)->after('tanggal_selesai');
        });

        DB::table('permohonan_tahapan')
            ->where('status', 'selesai')
            ->update([
                'tanggal_selesai' => DB::raw('updated_at'),
                'tanggal_selesai_perkiraan' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_tahapan', function (Blueprint $table) {
            $table->dropColumn(['tanggal_selesai', 'tanggal_selesai_perkiraan']);
        });
    }
};
