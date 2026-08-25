<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyeks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('komunitas_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pembuat_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('divisi_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('link_demo')->nullable();
            $table->string('link_repo')->nullable();
            $table->json('teknologi')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['komunitas_id', 'status']);
        });

        Schema::create('kelass', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('komunitas_id')->constrained()->cascadeOnDelete();
            $table->foreignId('divisi_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('jadwal_hari')->nullable();
            $table->string('jadwal_jam')->nullable();
            $table->string('tempat')->nullable();
            $table->timestamps();

            $table->index(['komunitas_id', 'divisi_id']);
        });

        Schema::create('materis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelass')->cascadeOnDelete();
            $table->string('judul');
            $table->enum('tipe', ['file', 'link']);
            $table->string('file_path')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['kelas_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materis');
        Schema::dropIfExists('kelass');
        Schema::dropIfExists('proyeks');
    }
};
