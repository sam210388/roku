<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class RealisasiSakti extends Controller
{
    public function getStatusSpm(Request $request, $nospm){
        $token = $request->header('token');
        if ($token == "samwitwicky"){
            $no = substr($nospm,0,6);
            $kdsatker = substr($nospm,7,6);
            $tahunanggaran = substr($nospm,14,4);
            $where = array(
                'kdsatker' => $kdsatker,
                'no_spp' => $no,
                'tahunanggaran' => $tahunanggaran
            );

            $infotagihan = DB::table('realisasi')->where($where)->limit(1)->get(['no_sp2d','tgl_sp2d']);

            header( "Content-type: application/json" );

            $data = array(
                "data" => $infotagihan
            );
            echo json_encode($data);
        }
    }
}
