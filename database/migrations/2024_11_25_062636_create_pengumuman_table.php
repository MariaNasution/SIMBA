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
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->enum('sumber', ['BEM', 'INFO', 'BURSAR', 'KEASRAMAAN', 'KEMAHASISWAAN'])
                ->comment('Sumber pengumuman');
            $table->string('judul')->comment('Judul pengumuman');
            $table->text('deskripsi')->comment('Deskripsi pengumuman');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
