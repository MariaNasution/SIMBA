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
        Schema::create('perwalians', function (Blueprint $table) {
            $table->id();
            $table->string('dosen_nip')->nullable(); // Foreign key or identifier for dosen
            $table->date('date');
            $table->timestamps();
            // Optional: Add foreign key constraint if 'dosens' table exists
            // $table->foreign('dosen_nip')->references('nip')->on('dosens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perwalians');
    }
};