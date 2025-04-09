<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifikasiTable extends Migration
{
    public function up()
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->bigIncrements('id_notifikasi');
            $table->text('pesan');
            $table->string('nim', 8);
            $table->unsignedBigInteger('id_konseling')->nullable();
            $table->unsignedBigInteger('id_perwalian')->nullable();
            $table->string('nama')->nullable();
            $table->timestamps();

            $table->foreign('nim')
                  ->references('nim')->on('mahasiswa')
                  ->onDelete('cascade');

            // Updated foreign key reference
            $table->foreign('id_konseling')
                  ->references('id')->on('request_konseling')
                  ->onDelete('cascade');

            $table->foreign('id_perwalian')
                  ->references('ID_Perwalian')->on('perwalian')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifikasi');
    }
}
