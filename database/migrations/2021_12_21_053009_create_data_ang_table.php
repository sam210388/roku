<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataAngTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_ang', function (Blueprint $table) {
            $table->id();
            $table->char('kdsatker',6);
            $table->char('kodeprogram',2);
            $table->char('kodekegiatan',4);
            $table->char('kodeoutput',3);
            $table->char('kdib',3);
            $table->integer('volumeoutput',false);
            $table->char('kodesuboutput',3);
            $table->integer('volumesuboutput',false);
            $table->char('kodekomponen',3);
            $table->char('kodesubkomponen',1);
            $table->text('uraiansubkomponen');
            $table->char('kodeakun',6);
            $table->char('kodejenisbeban',1);
            $table->char('kodecaratarik',1);
            $table->char('header1',1);
            $table->char('header2',1);
            $table->char('kodeitem',1);
            $table->char('nomoritem',1);
            $table->text('uraianitem');
            $table->char('sumberdana',1);
            $table->integer('volkeg1',false);
            $table->char('satkeg1',200);
            $table->integer('volkeg2',false);
            $table->char('satkeg2',200)->nullable();
            $table->integer('volkeg3',false);
            $table->char('satkeg3',200)->nullable();
            $table->integer('volkeg4',false);
            $table->char('satkeg4',200)->nullable();
            $table->integer('volkeg',false);
            $table->char('satkeg',200)->nullable();
            $table->integer('hargasat',false);
            $table->integer('total',false);
            $table->char('kodeblokir',200)->nullable();
            $table->integer('nilaiblokir',false);
            $table->char('kodestshistory',100);
            $table->integer('poknilai1',false);
            $table->integer('poknilai2',false);
            $table->integer('poknilai3',false);
            $table->integer('poknilai4',false);
            $table->integer('poknilai5',false);
            $table->integer('poknilai6',false);
            $table->integer('poknilai7',false);
            $table->integer('poknilai8',false);
            $table->integer('poknilai9',false);
            $table->integer('poknilai10',false);
            $table->integer('poknilai11',false);
            $table->integer('poknilai12',false);

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
        Schema::dropIfExists('data_ang');
    }
}
