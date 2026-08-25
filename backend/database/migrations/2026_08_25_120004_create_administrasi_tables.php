<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapats', function (Blueprint $table): void {
            $table->id();
            $table->string('judul');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->string('tempat')->nullable();
            $table->text('agenda')->nullable();
            $table->text('notulen')->nullable();
            $table->string('lampiran_path')->nullable();
            $table->foreignId('komunitas_id')->nullable()->constrained()->nullOnDelete();
            $table->string('qr_secret')->unique();
            $table->enum('status', ['dijadwalkan', 'selesai', 'dibatalkan'])->default('dijadwalkan');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'komunitas_id']);
        });

        Schema::create('rapat_member', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rapat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->enum('kehadiran', ['hadir', 'tidak', 'izin'])->nullable();
            $table->timestamp('waktu_absen')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['rapat_id', 'member_id']);
        });

        Schema::create('surat_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained()->cascadeOnDelete();
            $table->string('nama_jenis');
            $table->string('format');
            $table->unsignedInteger('counter')->default(0);
            $table->timestamps();

            $table->index(['periode_id', 'nama_jenis']);
        });

        Schema::create('surats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_template_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_surat');
            $table->string('pihak');
            $table->string('perihal');
            $table->string('file_path')->nullable();
            $table->text('disposisi')->nullable();
            $table->enum('status', ['draft', 'review', 'disetujui', 'terkirim'])->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['nomor_surat', 'periode_id']);
            $table->index(['jenis', 'tanggal_surat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
        Schema::dropIfExists('surat_templates');
        Schema::dropIfExists('rapat_member');
        Schema::dropIfExists('rapats');
    }
};
