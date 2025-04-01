<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestKonselingTable extends Migration
{
    public function up()
    {
        Schema::create('request_konseling', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 8);
            $table->string('nama_mahasiswa');
            $table->dateTime('tanggal_pengajuan');
            $table->text('deskripsi_pengajuan');
            $table->string('status');;
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('request_konseling');
    }
}
