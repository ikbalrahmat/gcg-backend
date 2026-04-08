<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('master_aspects', function (Blueprint $table) {
            $table->boolean('is_modifier')->default(false)->after('bobot');
        });
    }
    public function down(): void {
        Schema::table('master_aspects', function (Blueprint $table) {
            $table->dropColumn('is_modifier');
        });
    }
};
