<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBagianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bagian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iddeputi');
            $table->unsignedBigInteger('idbiro');
            $table->unsignedBigInteger('uraianbagian');
            $table->unsignedBigInteger('status');
            $table->foreign('iddeputi')->references('id')->on('deputi');
            $table->foreign('idbiro')->references('id')->on('biro');
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bagian');
    }
}
