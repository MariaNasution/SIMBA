<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('riwayat_daftar_request_konseling', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 8);
            $table->string('nama_mahasiswa');
            $table->dateTime('tanggal_pengajuan');
            $table->text('deskripsi_pengajuan');
            $table->text('alasan_penolakan')->required();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_daftar_request_konseling');
    }
};
