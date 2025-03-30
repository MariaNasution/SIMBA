<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('hasil_konseling', function (Blueprint $table) {
            $table->unsignedBigInteger('request_konseling_id')->after('id');
            $table->foreign('request_konseling_id')->references('id')->on('request_konseling')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('hasil_konseling', function (Blueprint $table) {
            $table->dropForeign(['request_konseling_id']);
            $table->dropColumn('request_konseling_id');
        });
    }
};
