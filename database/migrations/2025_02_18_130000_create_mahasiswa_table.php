<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User; // Import User model
use App\Models\Dosen; // Import Dosen model
use App\Models\Perwalian; // Import Perwalian model

class CreateMahasiswaTable extends Migration
{
    public function up()
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('nim', 8)->primary(); // Primary key, string type (e.g., 11S22010), length 8
            $table->string('username')->nullable(); // Foreign key to users table, can be null
            $table->string('nama') ->nullable(); // Student name
            $table->string('kelas') ->nullable(); // Class name
            $table->foreignIdFor(Dosen::class, 'ID_Dosen')->nullable(); // Foreign key to Dosen, renamed to 'ID_Dosen'
            $table->foreignIdFor(Perwalian::class, 'ID_Perwalian')->nullable(); // Foreign key to Perwalian, renamed to 'ID_Perwalian'
            $table->timestamps();

            // Define foreign key constraint for username manually (since it's a string)
            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
}