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
        Schema::table('evidences', function (Blueprint $table) {
            $table->string('factor_id')->nullable()->after('parameter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->dropColumn('factor_id');
        });
    }
};
