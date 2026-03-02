<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('pic_marketing', function (Blueprint $table) {
        $table->increments('id_pic');
        $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
        $table->string('nama_pic', 50);
        $table->unsignedInteger('down_convert')->default(0);
        $table->unsignedInteger('up_convert')->default(0);
        $table->integer('kpi_target')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pic_marketings');
    }
};
