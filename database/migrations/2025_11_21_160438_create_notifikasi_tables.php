<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title', 200);
            $table->text('message');
            $table->string('type', 50)->default('info'); // info, success, warning, danger
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        
        Schema::create('whatsapp_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('phone_number', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        
        // Tambahin foreign key constraint setelah table dibuat
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('whatsapp_log', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
        
        // Tambahin index setelah foreign key dibuat
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
            $table->index(['model_type', 'model_id']);
        });
        
        Schema::table('whatsapp_log', function (Blueprint $table) {
            $table->index('phone_number');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_log');
        Schema::dropIfExists('notifikasi');
    }
};