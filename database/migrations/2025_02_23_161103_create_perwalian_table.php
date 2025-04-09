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
            $table->string('username')->nullable();
            $table->enum('Status', ['Scheduled', 'Completed', 'Canceled']);
            $table->string('nama');
            $table->string('kelas')->nullable();
            $table->string('angkatan');
            $table->dateTime('Tanggal');
            $table->dateTime('Tanggal_Selesai')->nullable(); // Removed after('Tanggal')
            $table->string('role');
            $table->string('keterangan')->nullable(); // Removed after('role')
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('perwalian');
    }
}