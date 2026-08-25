<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('prodi')->nullable();
            $table->char('angkatan', 4);
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('link_portofolio')->nullable();
            $table->string('link_instagram')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'alumni'])->default('aktif');
            $table->timestamps();

            $table->index(['angkatan']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
