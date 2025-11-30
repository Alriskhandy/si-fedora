<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluasi', function (Blueprint $table) {
            $table->foreignId('rejected_by_kaban')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('evaluasi', function (Blueprint $table) {
            $table->dropForeign(['rejected_by_kaban']);
            $table->dropColumn(['rejected_by_kaban', 'rejected_at']);
        });
    }
};