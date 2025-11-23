<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal_fasilitasi', function (Blueprint $table) {
            $table->string('nama_kegiatan')->nullable()->change();
            // Atau tambah default value
            // $table->string('nama_kegiatan')->default('')->change();
        });
    }

    public function down()
    {
        Schema::table('jadwal_fasilitasi', function (Blueprint $table) {
            $table->string('nama_kegiatan')->nullable(false)->change();
        });
    }
};