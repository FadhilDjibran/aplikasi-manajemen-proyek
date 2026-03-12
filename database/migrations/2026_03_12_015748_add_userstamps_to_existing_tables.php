<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'transaksi_lead',
        'tipe_rumah',
        'projects',
        'pic_marketing',
        'leads',
        'follow_up',
        'users',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                    if (!Schema::hasColumn($tableName, 'created_by')) {
                        $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                    }

                    if (!Schema::hasColumn($tableName, 'updated_by')) {
                        $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
                    }

                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                    $table->dropForeign(['created_by']);
                    $table->dropForeign(['updated_by']);

                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn('created_by');
                    }
                    if (Schema::hasColumn($tableName, 'updated_by')) {
                        $table->dropColumn('updated_by');
                    }
                });
            }
        }
    }
};
