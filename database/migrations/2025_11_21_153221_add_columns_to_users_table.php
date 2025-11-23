<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('kabupaten_kota_id')->nullable()->after('email')->constrained('kabupaten_kota')->nullOnDelete();
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('nip', 30)->nullable()->after('phone');
            $table->string('jabatan', 100)->nullable()->after('nip');
            $table->text('alamat')->nullable()->after('jabatan');
            $table->string('foto')->nullable()->after('alamat');
            $table->boolean('is_active')->default(true)->after('foto');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kabupaten_kota_id']);
            $table->dropColumn([
                'kabupaten_kota_id',
                'phone',
                'nip',
                'jabatan',
                'alamat',
                'foto',
                'is_active',
                'last_login_at'
            ]);
        });
    }
};