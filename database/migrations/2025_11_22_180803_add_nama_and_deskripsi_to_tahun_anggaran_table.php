<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tahun_anggaran', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('tahun');
            $table->text('deskripsi')->nullable()->after('nama');
        });
    }

    public function down()
    {
        Schema::table('tahun_anggaran', function (Blueprint $table) {
            $table->dropColumn(['nama', 'deskripsi']);
        });
    }
};