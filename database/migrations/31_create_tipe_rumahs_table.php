<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('tipe_rumah', function (Blueprint $table) {
        $table->increments('id_tipe');
        $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
        $table->string('nama_tipe', 50);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipe_rumahs');
    }
};
