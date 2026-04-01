<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('leads', function (Blueprint $table) {
        $table->string('id_lead', 20)->primary();
        $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
        $table->date('tgl_masuk')->nullable();
        $table->string('nama_lead', 100);
        $table->string('no_whatsapp', 20)->nullable();
        $table->string('sumber_lead')->nullable()->change();

        $table->unsignedInteger('id_tipe_rumah_minat')->nullable();
        $table->foreign('id_tipe_rumah_minat')->references('id_tipe')->on('tipe_rumah');

        $table->decimal('perkiraan_budget', 15, 2)->nullable();

        $table->enum('status_lead', ['Tidak Prospek', 'Cold Lead', 'Warm Lead', 'Hot Prospek', 'Gagal Closing'])->nullable();

        $table->string('alasan_gagal', 100)->nullable();
        $table->text('catatan_gagal')->nullable();
        $table->date('tgl_gagal')->nullable();

        $table->unsignedInteger('id_pic')->nullable();
        $table->foreign('id_pic')->references('id_pic')->on('pic_marketing')->onDelete('set null');;

        $table->enum('cara_kontak', ['Whatsapp'])->nullable();
        $table->string('kota_domisili', 100)->nullable();
        $table->text('alamat')->nullable();
        $table->string('status_pekerjaan', 100)->nullable();
        $table->string('rencana_pembayaran')->nullable();
        $table->text('catatan')->nullable();
        $table->integer('follow_up_count')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
