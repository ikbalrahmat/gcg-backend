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
        Schema::create('document_requests', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('assessment_id');
            $table->string('assessment_year');
            $table->string('aspect_id');
            $table->string('indicator_id');
            $table->string('parameter_id');
            $table->string('parameter_name');
            $table->string('target_divisi');
            $table->string('requested_by');
            $table->string('request_date');
            $table->string('status')->default('Requested');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
