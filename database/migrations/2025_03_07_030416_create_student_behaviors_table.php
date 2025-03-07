<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentBehaviorsTable extends Migration
{
    public function up()
    {
        Schema::create('student_behaviors', function (Blueprint $table) {
            $table->id();
            $table->string('student_nim'); // The student's NIM to associate with the record
            $table->string('ta'); // Academic year, you can also use integer if preferred
            $table->integer('semester'); // e.g., 1 for Gasal, 2 for Genap, etc.
            $table->enum('type', ['pelanggaran', 'perbuatan_baik']);
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('poin')->default(0);
            $table->string('tindakan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_behaviors');
    }
}
