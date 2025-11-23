<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kabupaten_kota', function (Blueprint $table) {
            $table->string('kode', 50)->change(); // Ubah jadi 50 karakter
        });
    }

    public function down()
    {
        Schema::table('kabupaten_kota', function (Blueprint $table) {
            $table->string('kode', 20)->change(); // Kembali ke semula
        });
    }
};