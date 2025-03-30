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
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();
            $table->string('kelas'); // Nama kelas
            $table->integer('angkatan'); // Tahun angkatan
            $table->string('dosen_wali'); // Nama dosen wali
            $table->date('tanggal_perwalian'); // Tanggal perwalian
            $table->string('perihal_perwalian'); // Perihal perwalian
            $table->text('agenda_perwalian'); // Agenda perwalian
            $table->date('hari_tanggal_feedback'); // Tanggal feedback mahasiswa
            $table->string('perihal_feedback'); // Perihal feedback
            $table->text('catatan_feedback'); // Catatan mahasiswa
            $table->date('tanggal_ttd'); // Tanggal tanda tangan dosen wali
            $table->string('dosen_wali_ttd'); // Nama dosen wali yang menandatangani
            $table->foreignId('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};
