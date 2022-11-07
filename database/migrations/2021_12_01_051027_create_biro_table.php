<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biro', function (Blueprint $table) {
            $table->id();
            $table->char('uraianbiro',200);
            $table->unsignedBigInteger('iddeputi');

            $table->foreign('iddeputi')->references('id')->on('deputi');

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
        Schema::dropIfExists('biro');
    }
}
