<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Mahasiswa; // Import Mahasiswa model
use App\Models\Perwalian; // Import Perwalian model

class CreateNotifikasiTable extends Migration
{
    public function up()
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('ID_Notifikasi')->primary(); // Unique notification ID (auto-incrementing bigInteger)
            $table->text('Pesan'); // Notification message
            $table->string('NIM')->nullable(); // Student ID (foreign key to Mahasiswa), string type
            $table->string('nama');
            $table->foreignIdFor(Perwalian::class, 'Id_Perwalian')->nullable(); // Foreign key to Perwalian, renamed to 'Id_Perwalian'
            $table->timestamps();

            // Define foreign key constraint for NIM manually (since it's a string)
            $table->foreign('NIM')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifikasi');
    }
}