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
            $table->string('nim'); // Foreign key ke tabel mahasiswa
            $table->dateTime('tanggal_pengajuan');
            $table->text('deskripsi_pengajuan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        
            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('request_konseling');
    }
}