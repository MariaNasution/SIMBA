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
    Schema::create('nim', function (Blueprint $table) {
      $table->id();
      $table->string('nim')->unique(); // Kolom untuk NIM
      $table->string('nama')->nullable(); // Tambahkan kolom nama
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('nim');
  }
};
