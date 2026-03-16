<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->year('tahun')->default(date('Y'));
            $table->string('no_akun', 20);
            $table->unique(['project_id', 'tahun', 'no_akun']);
            $table->string('kategori_akun');
            $table->string('nama_akun');
            $table->enum('posisi_normal', ['Debit', 'Kredit']);
            $table->enum('jenis_laporan', ['Neraca', 'Laba Rugi']);

            $table->decimal('saldo_awal_debit', 15, 2)->default(0);
            $table->decimal('saldo_awal_kredit', 15, 2)->default(0);

            $table->decimal('saldo_akhir', 15, 2)->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
