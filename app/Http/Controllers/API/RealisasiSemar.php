<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealisasiSemar extends Controller
{
    public function getStatusKarwas(Request $request, $no_kwitansi_karwas){
        $token = $request->header('token');

        if ($token == "samwitwicky"){
            $where = array(
                'no_kwitansi_karwas' => $no_kwitansi_karwas
            );
            $infotagihan = DB::table('realisasisemar')->where($where)->limit(1)->get(['tahapan']);
            header( "Content-type: application/json" );

            $data = array(
                "data" => $infotagihan
            );
            echo json_encode($data);
        }else{
            $data = array(
                "data" => "Token Invalid"
            );
            echo json_encode($data);
        }
    }
}
