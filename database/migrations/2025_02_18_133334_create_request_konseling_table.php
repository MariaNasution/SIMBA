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
            $table->dateTime('tanggal_pengajuan');
            $table->text('deskripsi_pengajuan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_konseling');
    }
}