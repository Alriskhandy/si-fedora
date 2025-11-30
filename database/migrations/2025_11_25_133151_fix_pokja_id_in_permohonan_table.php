<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop foreign key lama
        Schema::table('permohonan', function (Blueprint $table) {
            $table->dropForeign(['pokja_id']);
            $table->dropColumn('pokja_id');
        });

        // Tambah kolom baru
        Schema::table('permohonan', function (Blueprint $table) {
            $table->foreignId('tim_pokja_id')->nullable()->constrained('tim_pokja')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('permohonan', function (Blueprint $table) {
            $table->dropForeign(['tim_pokja_id']);
            $table->dropColumn('tim_pokja_id');
        });

        Schema::table('permohonan', function (Blueprint $table) {
            $table->foreignId('pokja_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};