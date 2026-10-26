<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master OPD (provinsi/mitra/teknis) dan urusan pemerintahan yang menjadi bidangnya.
     */
    public function up(): void
    {
        Schema::create('opd', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('singkatan', 50)->nullable();
            $table->string('jenis', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('nama');
        });

        Schema::create('opd_urusan', function (Blueprint $table) {
            $table->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $table->foreignId('master_urusan_id')->constrained('master_urusan')->cascadeOnDelete();

            $table->primary(['opd_id', 'master_urusan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opd_urusan');
        Schema::dropIfExists('opd');
    }
};
