<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dosen_wali', function (Blueprint $table) {
            $table->string('username')->primary(); // Foreign key to users tableS
            $table->string('kelas')->nullable();
            $table->timestamps();

            // Define the foreign key constraint
            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('dosen_wali');
    }


};





    