<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswaTable extends Migration
{
    public function up()
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('username')->primary(); // Foreign key to users table
            $table->string('nim')->unique(); // Student ID
            $table->timestamps();

            // Define the foreign key constraint
            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
}