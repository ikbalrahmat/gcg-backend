<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assessment_members', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel assessments
            $table->string('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->string('name');
            $table->string('aspectId');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('assessment_members'); }
};
