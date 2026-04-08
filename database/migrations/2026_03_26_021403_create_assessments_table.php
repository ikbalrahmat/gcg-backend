<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assessments', function (Blueprint $table) {
            $table->string('id')->primary(); // Pakai string karena format React lu: ASSESS-12345
            $table->string('year');
            $table->string('tb'); // Tanggal Buku
            $table->string('no_st')->nullable();
            $table->string('pt'); // Pengendali Teknis
            $table->string('kt'); // Ketua Tim
            $table->string('status')->default('Draft');
            $table->string('created_by')->nullable();
            $table->json('data')->nullable(); // 🔑 INI WADAH UNTUK KERTAS KERJA
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('assessments'); }
};
