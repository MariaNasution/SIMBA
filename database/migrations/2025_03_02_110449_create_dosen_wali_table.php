<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Dosen; // Import Dosen model

class CreateDosenWaliTable extends Migration
{
    public function up()
    {
        Schema::create('dosen_wali', function (Blueprint $table) {
            $table->id('ID_Dosen_Wali'); // Auto-incrementing bigInteger primary key
            $table->string('nama'); // Lecturer name
            $table->softDeletes(); // Add soft deletes column
            $table->timestamps(); // Created_at and updated_at timestamps

        });
    }

    public function down()
    {
        Schema::dropIfExists('dosen_wali');
    }
}