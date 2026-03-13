<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_lead', function (Blueprint $table) {
            $table->enum('status_keuangan', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_lead', function (Blueprint $table) {
            $table->dropColumn('status_keuangan');
        });
    }
};
