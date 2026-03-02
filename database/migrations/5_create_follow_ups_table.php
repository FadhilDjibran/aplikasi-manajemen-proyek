<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('follow_up', function (Blueprint $table) {
        $table->increments('id_follow_up');
        $table->string('id_lead', 20)->nullable();
        $table->foreign('id_lead')->references('id_lead')->on('leads')->cascadeOnDelete();
        $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
        $table->unsignedInteger('id_pic')->nullable();
        $table->foreign('id_pic')->references('id_pic')->on('pic_marketing')->onDelete('set null');
        $table->date('tgl_follow_up')->nullable();
        $table->time('jam_follow_up')->nullable();
        $table->enum('channel_follow_up', ['Whatsapp'])->nullable();
        $table->text('hasil_follow_up')->nullable();
        $table->text('rencana_tindak_lanjut')->nullable();
        $table->date('tgl_follow_up_berikutnya')->nullable();
        $table->time('jam_follow_up_berikutnya')->nullable();
        $table->enum('status_follow_up', ['Sudah Dihubungi', 'Proses Follow Up', 'Siap Survey / Pertimbangan', 'Tidak Respons / Stop Follow Up'])->nullable();
        $table->date('tgl_survey')->nullable();
        $table->text('hasil_survey')->nullable();
        $table->text('catatan')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
