<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefstatusimportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refstatusimport', function (Blueprint $table) {
            $table->id();
            //$table->timestamps();
            $table->unsignedBigInteger('idrefstatus');
            $table->date('tanggalupdate');
            $table->foreign('idrefstatus')->references('id')->on('ref_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refstatusimport');
    }
}
