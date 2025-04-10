<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // gunakan UUID untuk identifikasi unik (opsional)
            $table->string('type');
            $table->string('notifiable_id');
            $table->string('notifiable_type');
            $table->text('data'); // data tambahan notifikasi dalam format JSON
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
