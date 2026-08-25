<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('surat_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['draft', 'review', 'disetujui', 'terkirim']);
            $table->string('catatan')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['surat_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_status_logs');
    }
};
