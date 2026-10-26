<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semua OPD adalah OPD lingkungan Provinsi, sehingga kolom jenis tidak diperlukan.
     */
    public function up(): void
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->string('jenis', 20)->default('provinsi')->after('singkatan');
        });
    }
};
