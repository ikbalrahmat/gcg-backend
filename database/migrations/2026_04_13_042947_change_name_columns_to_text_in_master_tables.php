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
        Schema::table('master_aspects', function (Blueprint $table) {
            $table->text('name')->change();
        });
        Schema::table('master_indicators', function (Blueprint $table) {
            $table->text('name')->change();
        });
        Schema::table('master_parameters', function (Blueprint $table) {
            $table->text('name')->change();
        });
        Schema::table('master_factors', function (Blueprint $table) {
            $table->text('name')->change();
        });
        Schema::table('master_sub_factors', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_aspects', function (Blueprint $table) {
            $table->string('name')->change();
        });
        Schema::table('master_indicators', function (Blueprint $table) {
            $table->string('name')->change();
        });
        Schema::table('master_parameters', function (Blueprint $table) {
            $table->string('name')->change();
        });
        Schema::table('master_factors', function (Blueprint $table) {
            $table->string('name')->change();
        });
        Schema::table('master_sub_factors', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
