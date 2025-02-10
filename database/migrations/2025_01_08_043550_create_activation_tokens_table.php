<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivationTokensTable extends Migration
{
    public function up()
    {
        Schema::create('activation_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('email');
            $table->string('password');
            $table->string('token');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activation_tokens');
    }
}
