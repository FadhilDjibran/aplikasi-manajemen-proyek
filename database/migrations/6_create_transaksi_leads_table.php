<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('transaksi_lead', function (Blueprint $table) {
        $table->increments('id_transaksi');
        $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
        $table->string('id_lead', 20)->nullable();
        $table->foreign('id_lead')->references('id_lead')->on('leads')->cascadeOnDelete();

        $table->enum('jenis_pembayaran', ['Booking', 'DP', 'Lunas'])->nullable();
        $table->decimal('nominal', 15, 2)->nullable();
        $table->date('tgl_pembayaran')->nullable();
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_leads');
    }
};
