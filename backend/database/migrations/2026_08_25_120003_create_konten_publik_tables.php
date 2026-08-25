<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('konten');
            $table->string('cover_path')->nullable();
            $table->string('kategori')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('lokasi')->nullable();
            $table->dateTime('mulai');
            $table->dateTime('selesai')->nullable();
            $table->foreignId('komunitas_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'batal'])->default('draft');
            $table->timestamps();

            $table->index(['mulai']);
            $table->index(['komunitas_id']);
        });

        Schema::create('galeri_albums', function (Blueprint $table): void {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('cover_path')->nullable();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['event_id']);
        });

        Schema::create('galeri_fotos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('album_id')->constrained('galeri_albums')->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['album_id']);
        });

        Schema::create('pengumumans', function (Blueprint $table): void {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->enum('prioritas', ['normal', 'penting'])->default('normal');
            $table->date('tayang_mulai')->nullable();
            $table->date('tayang_selesai')->nullable();
            $table->foreignId('komunitas_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['tayang_mulai', 'tayang_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
        Schema::dropIfExists('galeri_fotos');
        Schema::dropIfExists('galeri_albums');
        Schema::dropIfExists('events');
        Schema::dropIfExists('artikels');
    }
};
