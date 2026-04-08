<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('evidences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('assessment_id');
            $table->string('assessment_year');
            $table->string('aspect_id');
            $table->string('indicator_id');
            $table->string('parameter_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('divisi');
            $table->string('upload_date');
            $table->string('status')->default('Menunggu Verifikasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
