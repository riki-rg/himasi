<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_kategoris', function (Blueprint $table): void {
            $table->id();
            $table->string('nama')->unique();
            $table->enum('tipe_default', ['pemasukan', 'pengeluaran']);
            $table->timestamps();
        });

        Schema::create('kas', function (Blueprint $table): void {
            $table->id();
            $table->date('tanggal');
            $table->enum('tipe', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 12, 2);
            $table->foreignId('kas_kategori_id')->constrained()->restrictOnDelete();
            $table->foreignId('periode_id')->constrained()->cascadeOnDelete();
            $table->string('keterangan');
            $table->string('bukti_path')->nullable();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['periode_id', 'tipe']);
            $table->index(['tanggal']);
        });

        Schema::create('iurans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->decimal('jumlah', 12, 2);
            $table->foreignId('periode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('komunitas_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tenggat');
            $table->timestamps();
        });

        Schema::create('iuran_member', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('iuran_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['belum', 'lunas'])->default('belum');
            $table->foreignId('kas_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('lunas_pada')->nullable();
            $table->timestamps();

            $table->unique(['iuran_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iuran_member');
        Schema::dropIfExists('iurans');
        Schema::dropIfExists('kas');
        Schema::dropIfExists('kas_kategoris');
    }
};
