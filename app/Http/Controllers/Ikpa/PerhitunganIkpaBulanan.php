<?php

namespace App\Http\Controllers\Ikpa;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\Realisasi;
use App\Models\AnggaranRealisasi\RefStatus;
use App\Models\ReferensiUnit\Bagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\Decimal128;
use Yajra\DataTables\DataTables;

class PerhitunganIkpaBulanan extends Controller
{
    //munculkan dlu tampilan untuk melakukan perhitungan ikpa terbaru
    public function tampilmenurekap(){
        return view('ikpa.tampilmenurekapikpa',[
            'judul' => 'Penilaian IKPA'
        ]);
    }

    public function rekapdipabagian($tahunanggaran, $kdsatker, $periode){
        //buat periode
        switch ($periode){
            case 1:
                $bulan = 3;
                break;
            case 2:
                $bulan = 6;
                break;
            case 3:
                $bulan = 9;
                break;
            case 4:
                $bulan = 12;
        }

        //hapus data sumarydipabagian
        DB::table('summarydipabagian')->where('kdsatker','=',$kdsatker)->where('tahunanggaran','=',$tahunanggaran)->delete();
        //ambil data ref status terakhir per tanggal laporan
        //ambil idrefstatusterakhir
        $maxRevisiDIPA = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$kdsatker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOK = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$kdsatker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatus = ($maxRevisiPOK > $maxRevisiDIPA ? $maxRevisiPOK : $maxRevisiDIPA);

        //summary dipa bagian
        $datadipabagianperjenis = DB::table('bagian as a')
            ->select(['a.id as idbagian','b.jenisbelanja', DB::raw('sum(b.anggaran) as pagu')])
            ->leftJoin('sumarydipa as b', function($join) use($tahunanggaran, $idrefstatus, $kdsatker){
                $join->on('a.id','=','b.idbagian')
                    ->where('b.idrefstatus','=',$idrefstatus)
                    ->where('b.kdsatker','=',$kdsatker)
                    ->where('b.tahunanggaran','=',$tahunanggaran);
            })
            ->groupBy('a.id','b.jenisbelanja')
            ->get();

        foreach ($datadipabagianperjenis as $item){
            $idbagian = $item->idbagian;
            $jenisbelanja = $item->jenisbelanja;
            $pagu = $item->pagu;
            $datatarget = DB::table('prosentasetarget')->where('tahunanggaran','=',$tahunanggaran)
                ->where('jenisbelanja','=',$jenisbelanja)
                ->get();
            foreach ($datatarget as $itemdata){
                $targettw1 = (($itemdata->tw1)/100)*$pagu;
                $targettw2 = (($itemdata->tw2)/100)*$pagu;
                $targettw3 = (($itemdata->tw3)/100)*$pagu;
                $targettw4 = (($itemdata->tw4)/100)*$pagu;
            }

            $data = array(
                'tahunanggaran' => $tahunanggaran,
                'kdsatker' => $kdsatker,
                'idbagian' => $idbagian,
                'jenisbelanja' => $jenisbelanja,
                'pagu' => $pagu,
                'tw1' => $targettw1,
                'tw2' => $targettw2,
                'tw3' => $targettw3,
                'tw4' => $targettw4
            );

            DB::table('summarydipabagian')->insert($data);
        }
    }

    public function rekaprealisasibagiantriwulanan($tahunanggaran, $kdsatker){
        //summary dipa bagian
        $bagian = Bagian::all();
        foreach ($bagian as $b){
            $idbagian = $b->id;
            $datainsert = array(
                'tahunanggaran' => $tahunanggaran,
                'kdsatker' => $kdsatker,
                'idbagian' => $idbagian
            );
            $jenisbelanja = array('51','52','53');
            foreach ($jenisbelanja as $kode => $value){
                $jenisbelanja = $value;
                $datajenisbelanja = array(
                    'jenisbelanja' => $jenisbelanja
                );
                $databulanan = array();
                for ($i = 1; $i <=12; $i++){
                    if ($i==3 || $i == 6 || $i == 9 || $i == 12){
                        switch ($i){
                            case 3:
                                $tw = 'tw1';
                                break;
                            case 6:
                                $tw = 'tw2';
                                break;
                            case 9:
                                $tw = 'tw3';
                                break;
                            case 12:
                                $tw = 'tw4';
                                break;
                        }
                        $realisasibagiantriwulanan = DB::table('realisasi')
                            ->select([DB::raw('sum(nilairupiah) as realisasi')])
                            ->where('idbagian','=',$idbagian)
                            ->whereRaw('mid(pengenal, 23,2) = '.$jenisbelanja)
                            ->where('tahunanggaran','=',$tahunanggaran)
                            ->where('kdsatker','=',$kdsatker)
                            ->whereRaw('month(tgl_sp2d) <='.$i)
                            ->whereNotNull('no_sp2d')
                            ->value('realisasi');


                        $datatw = array(
                            $tw => $realisasibagiantriwulanan
                        );
                        $dataupdate = array_merge($databulanan, $datatw);
                        $whereupdate = array_merge($datainsert,$datajenisbelanja);
                        DB::table('summaryrealisasibagian')->where($whereupdate)->update($dataupdate);
                    }
                }

            }
        }
    }

    public function aksirekapnilaiikpa(Request $request){
        $kdsatker = $request->satker;
        $tahunanggaran = session('tahunanggaran');
        $periode = $request->periode;

        $this->rekapdipabagian($tahunanggaran, $kdsatker, $periode);
        $this->rekaprealisasibagiantriwulanan($tahunanggaran, $kdsatker, $periode);
        $this->perhitungannilairealisasitriwulanan($kdsatker);

    }

    public function perhitungannilairealisasitriwulanan($kdsatker){
        $tahunanggaran = session('tahunanggaran');

        //ambil data bagian
        $bagian = Bagian::all();
        foreach ($bagian as $b){
            $idbagian = $b->id;
            $datainsert = array(
                'tahunanggaran' => $tahunanggaran,
                'kdsatker' => $kdsatker,
                'idbagian' => $idbagian
            );

            for($i = 1; $i<=12; $i++){
                if ($i == 3 || $i == 6 || $i == 9 || $i == 12){
                    switch ($i){
                        case 3:
                            $tw = 'tw1';
                            break;
                        case 6:
                            $tw = 'tw2';
                            break;
                        case 9:
                            $tw = 'tw3';
                            break;
                        case 12:
                            $tw = 'tw4';
                            break;
                    }

                    //dapatkan sumary anggaran
                    $anggaran = DB::table('summarydipabagian')
                        ->select([DB::raw('sum('.$tw.') as anggaran')])
                        ->where('idbagian','=',$idbagian)
                        ->where('kdsatker','=',$kdsatker)
                        ->where('tahunanggaran','=',$tahunanggaran)
                        ->value('anggaran');

                    $realisasi = DB::table('summaryrealisasibagian')
                        ->select([DB::raw('sum('.$tw.') as realisasi')])
                        ->where('idbagian','=',$idbagian)
                        ->where('kdsatker','=',$kdsatker)
                        ->where('tahunanggaran','=',$tahunanggaran)
                        ->value('realisasi');

                    if ($anggaran > 0) {
                        $nilai = ($realisasi / $anggaran) * 100;
                        if ($nilai >= 100){
                            $nilaitw = array(
                                $tw => 100
                            );
                        }else{
                            $nilai = number_format($nilai,2,'.',',');
                            $nilaitw = array(
                                $tw => $nilai
                            );
                        }
                        DB::table('summarynilairealisasi')->where($datainsert)->update($nilaitw);
                    }
                }
            }
        }
    }
}
