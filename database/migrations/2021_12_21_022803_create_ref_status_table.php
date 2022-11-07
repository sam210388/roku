<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idrefstatus');
            $table->char('kode_kementerian',3);
            $table->char('kdsatker',6);
            $table->char('kd_sts_history',10);
            $table->char('jenis_revisi',100);
            $table->unsignedBigInteger('revisi_ke',false);
            $table->unsignedBigInteger('pagu_belanja',false);
            $table->char('no_dipa',100);
            $table->date('tgl_dipa');
            $table->date('tgl_revisi');
            $table->char('approve',1);
            $table->char('approve_span',1);
            $table->char('validated',1);
            $table->char('flag_update_coa',1);
            $table->char('owner',50);
            $table->char('digital_stamp',50);
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
        Schema::dropIfExists('ref_status');
    }
}
