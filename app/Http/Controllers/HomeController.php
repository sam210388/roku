<?php

namespace App\Http\Controllers;

use App\Models\AnggaranRealisasi\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AnggaranRealisasi\TimeStampData;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tahunanggaran = session('tahunanggaran');
        $maxrevisidipasetjen = DB::table('ref_status')
                                ->where('kdsatker','=','001012')
                                ->where('tahunanggaran','=',$tahunanggaran)
                                ->where('kd_sts_history','LIKE','B%')
                                ->max('revisi_ke');
        $maxrevisipoksetjen = DB::table('ref_status')
            ->where('kdsatker','=','001012')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->where('kd_sts_history','LIKE','C%')
            ->where('flag_update_coa','=',1)
            ->max('revisi_ke');
        $maxrevisidipadewan = DB::table('ref_status')
            ->where('kdsatker','=','001030')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->where('kd_sts_history','LIKE','B%')
            ->max('revisi_ke');
        $maxrevisipokdewan = DB::table('ref_status')
            ->where('kdsatker','=','001030')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->where('kd_sts_history','LIKE','C%')
            ->where('flag_update_coa','=',1)
            ->max('revisi_ke');
        $lastupdate = TimeStampData::value('updaterefstatus');

        //info card realisasi
        $realisasisetjenselesai = Realisasi::where([
            ['kdsatker','=','001012'],
            ['tahunanggaran','=',$tahunanggaran]
        ])->whereNotNull('no_sp2d')->sum('nilairupiah');
        $realisasisetjenproses = Realisasi::where([
            ['kdsatker','=','001012'],
            ['tahunanggaran','=',$tahunanggaran]
        ])->whereNull('no_sp2d')->sum('nilairupiah');

        $realisasidewanselesai = Realisasi::where([
            ['kdsatker','=','001030'],
            ['tahunanggaran','=',$tahunanggaran]
        ])->whereNotNull('no_sp2d')->sum('nilairupiah');

        $realisasidewanproses = Realisasi::where([
            ['kdsatker','=','001030'],
            ['tahunanggaran','=',$tahunanggaran]
        ])->whereNull('no_sp2d')->sum('nilairupiah');
        $realisasisetjenselesai = number_format($realisasisetjenselesai,0,',','.');
        $realisasisetjenproses = number_format($realisasisetjenproses,0,',','.');
        $realisasidewanselesai = number_format($realisasidewanselesai,0,',','.');
        $realisasidewanproses = number_format($realisasidewanproses,0,',','.');
        $lastupdate = TimeStampData::value('updaterealisasi');
        $info = array(
            'realisasisetjenselesai' => $realisasisetjenselesai,
            'realisasisetjenproses' => $realisasisetjenproses,
            'realisasidewanselesai' => $realisasidewanselesai,
            'realisasidewanproses' => $realisasidewanproses,
            'waktuupdate' => $lastupdate
        );
        return view('home',
            [
                'maxrevisidipasetjen' => $maxrevisidipasetjen,
                'maxrevisidipadewan' => $maxrevisidipadewan,
                'maxrevisipoksetjen' => $maxrevisipoksetjen,
                'maxrevisipokdewan' => $maxrevisipokdewan,
                'waktuupdate' => $lastupdate,
                'info' => $info
            ]);
    }
}
