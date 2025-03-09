<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerwalianTable extends Migration
{
    public function up()
    {
        Schema::create('perwalian', function (Blueprint $table) {
            $table->id('ID_Perwalian')->primary(); // Auto-incrementing primary key
            $table->string('ID_Dosen_Wali')->nullable(); // Foreign key to dosen.nip (string)
            $table->string('Status')->default('pending'); // Status of the perwalian
            $table->date('Tanggal'); // Date of the perwalian
            $table->timestamps();

            // Define foreign key constraint manually to reference nip
            $table->foreign('ID_Dosen_Wali')->references('nip')->on('dosen')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('perwalian');
    }
}