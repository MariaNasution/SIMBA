<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Dosen;

class CreateMahasiswaTable extends Migration
{
    public function up()
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('nim', 8)->primary();
            $table->string('username')->nullable();
            $table->foreignIdFor(Dosen::class, 'ID_Dosen')->nullable();
            $table->unsignedBigInteger('ID_Perwalian')->nullable();
            $table->string('nama')->nullable();
            $table->string('kelas')->nullable();
            $table->timestamps();

            // $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            $table->foreign('ID_Perwalian')->references('ID_Perwalian')->on('perwalian')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
}