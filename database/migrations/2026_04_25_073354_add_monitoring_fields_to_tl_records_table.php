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
        Schema::table('tl_records', function (Blueprint $table) {
            $table->text('rencanaTindakLanjut')->nullable();
            $table->string('linkEvidence', 500)->nullable();
            $table->text('areaPerbaikan')->nullable();
            $table->text('hasilTindakLanjut')->nullable();
            $table->string('realisasiTindakLanjut')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tl_records', function (Blueprint $table) {
            $table->dropColumn([
                'rencanaTindakLanjut',
                'linkEvidence',
                'areaPerbaikan',
                'hasilTindakLanjut',
                'realisasiTindakLanjut'
            ]);
        });
    }
};
