<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('permohonan', function (Blueprint $table) {
            $table->foreignId('pokja_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('permohonan', function (Blueprint $table) {
            $table->dropForeign(['pokja_id']);
            $table->dropColumn('pokja_id');
        });
    }
};