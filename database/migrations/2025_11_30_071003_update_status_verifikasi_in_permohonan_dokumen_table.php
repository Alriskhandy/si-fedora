<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // PostgreSQL: Drop constraint lama, tambah nilai baru ke enum, buat constraint baru
        DB::statement("ALTER TABLE permohonan_dokumen DROP CONSTRAINT IF EXISTS permohonan_dokumen_status_verifikasi_check");

        DB::statement("ALTER TABLE permohonan_dokumen ADD CONSTRAINT permohonan_dokumen_status_verifikasi_check 
            CHECK (status_verifikasi IN ('pending', 'verified', 'rejected', 'revision_required'))");
    }

    public function down()
    {
        DB::statement("ALTER TABLE permohonan_dokumen DROP CONSTRAINT IF EXISTS permohonan_dokumen_status_verifikasi_check");

        DB::statement("ALTER TABLE permohonan_dokumen ADD CONSTRAINT permohonan_dokumen_status_verifikasi_check 
            CHECK (status_verifikasi IN ('pending', 'verified', 'rejected'))");
    }
};
