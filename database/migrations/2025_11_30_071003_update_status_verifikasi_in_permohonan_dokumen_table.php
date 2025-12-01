<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            // Ganti enum jadi sesuai kebutuhan
            $table->enum('status_verifikasi', [
                'pending', 
                'verified', 
                'rejected', 
                'revision_required'  // <-- Tambahin ini
            ])->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('permohonan_dokumen', function (Blueprint $table) {
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending')->change();
        });
    }
};