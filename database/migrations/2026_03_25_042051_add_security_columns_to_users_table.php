<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahan untuk fitur keamanan
            $table->integer('failed_attempts')->default(0)->after('password');
            $table->boolean('is_locked')->default(false)->after('failed_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['failed_attempts', 'is_locked']);
        });
    }
};
