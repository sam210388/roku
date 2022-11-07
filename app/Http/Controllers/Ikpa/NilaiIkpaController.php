<?php

namespace App\Http\Controllers\Ikpa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class NilaiIkpaController extends Controller
{
    public function nilaiikpabagian(){

        return view('ikpa.nilaiikparealisasi',[
            'judul' => 'Nilai IKPA Per Bagian',
        ]);
    }

    public function getnilaiikparealisasi(Request $request,$kdsatker, $periode){
        if ($request == ajax()){
            $tahunanggaran = session('tahunanggaran');
            $datanilaiikpabagian = DB::table('summarynilairealisasi as a')
                ->select(['a.tw1 as tw1','a.tw2 as tw2','a.tw3 as tw3','a.tw4 as tw4','b.uraianbagian as bagian'])
                ->leftJoin('bagian as b','a.idbagian','=','b.id')
                ->where('tahunanggaran','='.$tahunanggaran)
                ->get();

            return Datatables::of($datanilaiikpabagian)
                ->addIndexColumn()
                ->addColumn('tw1',function($row){
                    $tw1 = $row->tw1;
                    $tw1 = number_format($tw1,0,',','.');
                    return $tw1;

                })
                ->addColumn('tw2',function($row){
                    $tw1 = $row->tw2;
                    $tw1 = number_format($tw1,0,',','.');
                    return $tw1;

                })
                ->addColumn('tw3',function($row){
                    $tw1 = $row->tw3;
                    $tw1 = number_format($tw1,0,',','.');
                    return $tw1;

                })
                ->addColumn('tw4',function($row){
                    $tw1 = $row->tw4;
                    $tw1 = number_format($tw1,0,',','.');
                    return $tw1;

                })
                ->rawColumns(['bagian','tw1', 'tw2', 'tw3', 'tw4'])
                ->make(true);
        }

    }
}
