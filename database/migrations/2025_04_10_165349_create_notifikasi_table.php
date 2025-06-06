@ -1,33 +1,33 @@
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Perwalian; // Import Perwalian model
use App\Models\RequestKonseling; // Import Notifikasi model

class CreateNotifikasiTable extends Migration
{
    public function up()
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('ID_Notifikasi')->primary(); // Unique notification ID (auto-incrementing bigInteger)
            $table->text('Pesan'); // Notification message
            $table->string('NIM')->nullable(); // Student ID (foreign key to Mahasiswa), string type
            $table->foreignIdFor(RequestKonseling::class, 'Id_Konseling')->nullable(); // Foreign key to Konseling, renamed to 'Id_Konseling'
            $table->foreignIdFor(Perwalian::class, 'Id_Perwalian')->nullable(); // Foreign key to Perwalian, renamed to 'Id_Perwalian'
            $table->string('nama')->nullable(); // Student ID (foreign key to Mahasiswa), string type   
            $table->string('role')->nullable();
            $table->timestamps();

            // Define foreign key constraint for NIM manually (since it's a string)
            //$table->foreign('NIM')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifikasi');
    }
}
