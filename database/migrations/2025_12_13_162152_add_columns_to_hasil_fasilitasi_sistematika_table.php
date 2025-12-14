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
        Schema::table('hasil_fasilitasi_sistematika', function (Blueprint $table) {
            $table->foreignId('master_bab_id')->nullable()->after('hasil_fasilitasi_id')->constrained('master_bab')->nullOnDelete();
            $table->string('sub_bab')->nullable()->after('master_bab_id');
            $table->foreignId('user_id')->nullable()->after('catatan_penyempurnaan')->constrained('users')->nullOnDelete();
            
            // Buat bab_sub_bab nullable karena sekarang pakai master_bab_id
            $table->string('bab_sub_bab')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_fasilitasi_sistematika', function (Blueprint $table) {
            $table->dropForeign(['master_bab_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['master_bab_id', 'sub_bab', 'user_id']);
            
            $table->string('bab_sub_bab')->nullable(false)->change();
        });
    }
};
