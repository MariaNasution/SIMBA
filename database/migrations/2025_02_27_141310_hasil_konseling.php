<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('hasil_konseling', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('nim')->unique();
        $table->string('file'); // Menyimpan path file
        $table->text('keterangan')->nullable();
        $table->timestamps(); // Tambahkan ini
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_konseling'); // Sebelumnya salah: 'mahasiswa_konseling'
    }

};
