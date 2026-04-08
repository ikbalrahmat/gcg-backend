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
        Schema::create('tl_records', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('assessment_id');
            $table->string('status')->default('Open');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->text('auditee_note')->nullable();
            $table->text('auditor_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tl_records');
    }
};
