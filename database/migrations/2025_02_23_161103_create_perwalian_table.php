<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DosenWali; // Import DosenWali model

class CreatePerwalianTable extends Migration
{
    public function up()
    {
        Schema::create('perwalian', function (Blueprint $table) {
            $table->id('ID_Perwalian')->primary(); 
            $table->foreignIdFor(DosenWali::class, 'ID_Dosen_Wali')->nullable(); // Foreign key to DosenWali, renamed to 'ID_Dosen_Wali'
            $table->string('Status')->default('pending'); // Status of the perwalian
            $table->date('Tanggal'); // Date of the perwalian
            $table->timestamps();

            // No need for a manual foreign key constraint for ID_Dosen_Wali; constrained() handles it
        });
    }

    public function down()
    {
        Schema::dropIfExists('perwalian');
    }
}