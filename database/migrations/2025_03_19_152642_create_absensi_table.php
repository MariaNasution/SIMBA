<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('ID_Absensi'); // Auto-incrementing primary key
            $table->string('nim'); // Foreign key reference to mahasiswa
            $table->enum('status_kehadiran', ['hadir', 'alpa', 'izin']);
            $table->string('kelas'); // Class info
            $table->timestamps(); // Created_at & Updated_at

            // Foreign key constraint
            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi');
    }
};
