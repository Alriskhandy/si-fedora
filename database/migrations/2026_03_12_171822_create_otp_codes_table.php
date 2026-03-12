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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('phone', 20);
            $table->string('code'); // Hashed OTP
            $table->timestamp('expired_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['phone', 'expired_at']);
            $table->index(['user_id', 'expired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
