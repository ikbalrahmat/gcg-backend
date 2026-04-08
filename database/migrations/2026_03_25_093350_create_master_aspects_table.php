<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('master_aspects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('bobot', 8, 3)->default(0); // decimal untuk nyimpen bobot/persen
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('master_aspects'); }
};
