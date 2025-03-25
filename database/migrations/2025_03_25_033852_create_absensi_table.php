<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensiTable extends Migration
{
    public function up()
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('ID_Absensi');
            $table->unsignedBigInteger('ID_Perwalian')->nullable();
            $table->string('nim');
            $table->enum('status_kehadiran', ['hadir', 'alpa', 'izin']);
            $table->string('kelas');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('ID_Perwalian')->references('ID_Perwalian')->on('perwalian')->onDelete('cascade');
            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi');
    }
}