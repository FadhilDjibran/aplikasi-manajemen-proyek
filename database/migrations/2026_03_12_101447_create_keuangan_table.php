<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('no_akun', 20);
            $table->foreign('no_akun')->references('no_akun')->on('coa')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('input', ['Kas Besar', 'Kas Kecil', 'Bank', 'Jurnal']);
            $table->string('jenis_penggunaan')->nullable();
            $table->text('keterangan');
            $table->string('bukti')->nullable();

            $table->decimal('mutasi_masuk', 15, 2)->default(0);
            $table->decimal('mutasi_keluar', 15, 2)->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
