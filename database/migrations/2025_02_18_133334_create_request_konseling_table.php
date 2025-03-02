<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_konseling', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nama');
            $table->string('nim')->unique();
            $table->dateTime('tanggal_pengajuan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('deskripsi_pengajuan');
            $table->timestamps(); // Created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_konseling');
    }
};