<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('status_kehadiran');
        });
    }

    public function down()
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};