<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerwalianTable extends Migration
{
    public function up()
    {
        Schema::create('perwalian', function (Blueprint $table) {
            $table->id('ID_Perwalian');
            $table->string('ID_Dosen_Wali')->nullable();
            $table->enum('Status', ['Scheduled', 'Completed', 'Canceled']);
            $table->string('nama');
            $table->string('kelas');
            $table->date('Tanggal');
            $table->timestamps();

            $table->foreign('ID_Dosen_Wali')->references('nip')->on('dosen')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('perwalian');
    }
}