<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Perwalian; // Import Perwalian model

class CreateAbsensiTable extends Migration
{
    public function up()
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('ID_Absensi')->primary(); // Auto-incrementing bigInteger primary key for attendance record
            $table->string('Kelas'); // Attendance status (e.g., 'hadir', 'tidak hadir'), default to 'hadir'
            $table->timestamps();

            // No need for a manual foreign key constraint for ID_Perwalian; constrained() handles it
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi');
    }
}