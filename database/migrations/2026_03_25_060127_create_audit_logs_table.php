<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Siapa yang ngelakuin aksi (bisa null kalau aksinya login gagal/belum punya sesi)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Jenis aksi: 'login_sukses', 'login_gagal', 'created', 'updated', 'deleted'
            $table->string('action');

            // Tabel/Model apa yang diubah (misal: App\Models\User)
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            // Simpan data lama dan baru (biar tau apa yang diubah)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Data pelacakan (Requirement No. 9)
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable(); // Nyatet browser & OS yang dipake

            $table->timestamps(); // Udah otomatis nyatet Date & Time Stamp
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
