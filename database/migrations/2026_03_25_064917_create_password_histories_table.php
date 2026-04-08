<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users (kalau user dihapus, riwayatnya ikut kehapus)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Menyimpan hash password lama
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
    }
};
