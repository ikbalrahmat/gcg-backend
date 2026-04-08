<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('assessments', function (Blueprint $table) {
            // Pakai longText karena kita nyimpen file PDF dalam bentuk Base64 yang teksnya sangat panjang
            $table->longText('final_report_url')->nullable();
            $table->string('final_report_name')->nullable();
        });
    }
    public function down(): void {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['final_report_url', 'final_report_name']);
        });
    }
};
