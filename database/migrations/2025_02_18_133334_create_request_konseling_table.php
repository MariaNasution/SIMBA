<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestKonselingTable extends Migration
{
    public function up()
    {
        Schema::create('request_konselings', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 8);
            $table->string('nama_mahasiswa');
            $table->dateTime('tanggal_pengajuan');
            $table->text('deskripsi_pengajuan');
            $table->string('status')->default('pending');
            $table->timestamps();

            // Foreign key ke tabel mahasiswa
            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }



    public function down()
    {
        Schema::dropIfExists('request_konseling');
    }
}
