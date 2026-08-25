<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['aktif', 'arsip'])->default('aktif');
            $table->timestamps();

            $table->index(['status']);
        });

        Schema::create('komunitas', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('divisis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('komunitas_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['periode_id', 'komunitas_id']);
        });

        Schema::create('jabatans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('divisi_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->enum('tingkat', ['utama', 'staf', 'anggota'])->default('staf');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['divisi_id']);
        });

        Schema::create('penugasans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jabatan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['member_id', 'jabatan_id', 'periode_id']);
        });

        Schema::create('komunitas_member', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('komunitas_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'komunitas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komunitas_member');
        Schema::dropIfExists('penugasans');
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('divisis');
        Schema::dropIfExists('komunitas');
        Schema::dropIfExists('periodes');
    }
};
