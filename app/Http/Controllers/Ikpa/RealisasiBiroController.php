<?php

namespace App\Http\Controllers\Ikpa;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\RefStatus;
use App\Models\ReferensiUnit\Biro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use DateTime;

class RealisasiBiroController extends Controller
{
    public function realisasibiro(){
        $judul = 'Realisasi Biro';
        return view('ikpa.realisasibiro',['judul' => $judul]);
    }

    public function getRealisasiPerBiro(Request $request)
    {
        if ($request->ajax()) {
            $tahunanggaran = session('tahunanggaran');
            $satkerdewan = '001030';
            $satkersetjen = '001012';

            //ambil bulan
            $tanggalserver = new DateTime();
            $bulan = $tanggalserver->format('n');

            //ambil data revisi setjen
            $maxRevisiDipaSetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
            $maxRevisiPOKSetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
            $idrefstatusSetjen = ($maxRevisiPOKSetjen > $maxRevisiDipaSetjen ? $maxRevisiPOKSetjen : $maxRevisiDipaSetjen);
            //echo json_encode($idrefstatusSetjen);

            //ambil data revisi dewan
            $maxRevisiDipaDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
            $maxRevisiPOKDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
            $idrefstatusDewan = ($maxRevisiPOKDewan > $maxRevisiDipaDewan ? $maxRevisiPOKDewan : $maxRevisiDipaDewan);

           $datarealisasisetjen = DB::table('biro as a')
                ->select(['a.idbiro as idbiro','a.kdsatker as satker','a.uraianbiro as biro',
                    'realisasisemar' => function($query) use ($tahunanggaran, $bulan, $satkersetjen){
                        $query->select([DB::raw('sum(e.nilai_tagihan)')])
                            ->from('realisasisemar as e')
                            ->whereColumn('a.idbiro','e.idbiro')
                            ->where('e.kdsatker','=',$satkersetjen)
                            ->whereRaw('month(e.tanggal_kwitansi_karwas) <= '.$bulan)
                            ->whereRaw('year(e.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                    },
                    'diparevisi' => function($query) use ($tahunanggaran, $satkersetjen, $idrefstatusSetjen){
                        $query->select([DB::raw('sum(b.anggaran)')])
                            ->from('sumarydipa as b')
                            ->whereColumn('a.idbiro','b.idbiro')
                            ->where('b.kdsatker','=',$satkersetjen)
                            ->where('b.tahunanggaran','=',$tahunanggaran)
                            ->where('b.idrefstatus','=',$idrefstatusSetjen);
                    },'realisasisakti' => function($query) use ($tahunanggaran, $satkersetjen, $idrefstatusSetjen){
                        $query->select([DB::raw('sum(c.nilairupiah)')])
                            ->from('realisasi as c')
                            ->whereColumn('a.idbiro','c.idbiro')
                            ->where('c.kdsatker','=',$satkersetjen)
                            ->where('c.tahunanggaran','=',$tahunanggaran);
                        }])
                ->where('a.kdsatker','=',$satkersetjen)
                ->groupBy('a.kdsatker','a.idbiro');

            $datarealisasiplusdewan = DB::table('biro as a')
                ->select(['a.idbiro as idbiro','a.kdsatker as satker','a.uraianbiro as biro',
                    'realisasisemar' => function($query) use ($tahunanggaran, $bulan, $satkerdewan){
                        $query->select([DB::raw('sum(e.nilai_tagihan)')])
                            ->from('realisasisemar as e')
                            ->whereColumn('a.idbiro','e.idbiro')
                            ->where('e.kdsatker','=',$satkerdewan)
                            ->whereRaw('month(e.tanggal_kwitansi_karwas) <= '.$bulan)
                            ->whereRaw('year(e.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                    },
                    'diparevisi' => function($query) use ($tahunanggaran, $satkerdewan, $idrefstatusDewan){
                        $query->select([DB::raw('sum(b.anggaran)')])
                            ->from('sumarydipa as b')
                            ->whereColumn('a.idbiro','b.idbiro')
                            ->where('b.kdsatker','=',$satkerdewan)
                            ->where('b.tahunanggaran','=',$tahunanggaran)
                            ->where('b.idrefstatus','=',$idrefstatusDewan);
                    },'realisasisakti' => function($query) use ($tahunanggaran, $satkerdewan){
                        $query->select([DB::raw('sum(c.nilairupiah)')])
                            ->from('realisasi as c')
                            ->whereColumn('a.idbiro','c.idbiro')
                            ->where('c.kdsatker','=',$satkerdewan)
                            ->where('c.tahunanggaran','=',$tahunanggaran);
                    }])
                ->where('a.kdsatker','=',$satkerdewan)
                ->union($datarealisasisetjen)
                ->groupBy('a.kdsatker','a.idbiro')
                ->get();

            return Datatables::of($datarealisasiplusdewan)
                ->addIndexColumn()

                ->addColumn('biro',function($row){
                    $uraianbiro = $row->biro;
                    $idbiro = $row->idbiro;
                    $satker = $row->satker;
                    $linklistdata = url('/ikpa/tampilrealisasibagian/'.$idbiro.'/'.$satker);
                    $actionBtn = '<a class="btn btn-success text-white btn-sm" href="'.$linklistdata.'">'.$uraianbiro.'</a>';
                    return $actionBtn;
                })
                ->addColumn('diparevisi',function($row){
                    $diparevisi = $row->diparevisi;
                    $diparevisi = number_format($diparevisi,0,',','.');
                    return $diparevisi;

                })
                ->addColumn('realisasisakti',function($row){
                    $realisasisakti = $row->realisasisakti;
                    $realisasisakti = number_format($realisasisakti,0,',','.');
                    $idbiro = $row->idbiro;
                    $satker = $row->satker;
                    $linklistdata = url('/ikpa/tampildatasakti/'.$idbiro.'/'.$satker);
                    $actionBtn = '<a class="btn btn-info text-white btn-sm" href="'.$linklistdata.'">'.$realisasisakti.'</a>';
                    return $actionBtn;

                })
                ->addColumn('realisasisemar',function($row){
                    $realisasisemar = $row->realisasisemar;
                    $realisasisemar = number_format($realisasisemar,0,',','.');
                    $idbiro = $row->idbiro;
                    $satker = $row->satker;
                    $linklistdata = url('/ikpa/tampildatasemar/'.$idbiro.'/'.$satker);
                    $actionBtn = '<a class="btn btn-info text-white btn-sm" href="'.$linklistdata.'">'.$realisasisemar.'</a>';
                    return $actionBtn;

                })

                ->addColumn('prosentasesakti',function($row){
                    $realisasisakti = $row->realisasisakti;
                    $diparevisi = $row->diparevisi;
                    if ($diparevisi == 0){
                        $prosentase = 0;
                    }else{
                        $prosentase = ($realisasisakti/$diparevisi)*100;
                        $prosentase = number_format($prosentase,2,',','.');
                    }
                    return $prosentase;

                })

                ->addColumn('prosentasesemar',function($row){
                    $realisasisemar = $row->realisasisemar;
                    $diparevisi = $row->diparevisi;
                    if ($diparevisi == 0){
                        $prosentase = 0;
                    }else{
                        $prosentase = ($realisasisemar/$diparevisi)*100;
                        $prosentase = number_format($prosentase,2,',','.');
                    }
                    return $prosentase;
                })

                ->rawColumns(['biro','realisasisemar','diparevisi','realisasisakti','prosentasesakti','prosentasesemar'])
                ->make(true);
        }
    }

    public function tampilrealisasibagian($idbiro, $kdsatker){
        $judul = 'Realisasi Per Bagian';
        $uraianbiro = Biro::where('id','=',$idbiro)->value('uraianbiro');

        return view('ikpa.realisasibagian',[
           'judul' => $judul,
           'kdsatker' => $kdsatker,
           'idbiro' => $idbiro,
           'uraianbiro' => $uraianbiro
        ]);
    }

    public function getdatarealisasibagian(Request $request, $idbiro, $kdsatker){
        if ($request->ajax()) {
            $tahunanggaran = session('tahunanggaran');

            //ambil bulan
            $tanggalserver = new DateTime();
            $bulan = $tanggalserver->format('n');

            //ambil data revisi satker
            $maxRevisiDipa = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$kdsatker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
            $maxRevisiPOK = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$kdsatker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
            $idrefstatus = ($maxRevisiPOK > $maxRevisiDipa ? $maxRevisiPOK : $maxRevisiDipa);

            $datarealisasi = DB::table('bagian as a')
                ->select(['a.id as idbagian','a.uraianbagian as bagian',
                    'realisasisemar' => function($query) use ($tahunanggaran, $bulan, $kdsatker){
                        $query->select([DB::raw('sum(e.nilai_tagihan)')])
                            ->from('realisasisemar as e')
                            ->whereColumn('a.id','e.idbagian')
                            ->where('e.kdsatker','=',$kdsatker)
                            ->whereRaw('month(e.tanggal_kwitansi_karwas) <= '.$bulan)
                            ->whereRaw('year(e.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                    },
                    'diparevisi' => function($query) use ($tahunanggaran, $kdsatker, $idrefstatus){
                        $query->select([DB::raw('sum(b.anggaran)')])
                            ->from('sumarydipa as b')
                            ->whereColumn('a.id','b.idbagian')
                            ->where('b.kdsatker','=',$kdsatker)
                            ->where('b.tahunanggaran','=',$tahunanggaran)
                            ->where('b.idrefstatus','=',$idrefstatus);
                    },'realisasisakti' => function($query) use ($tahunanggaran, $kdsatker, $idrefstatus){
                        $query->select([DB::raw('sum(c.nilairupiah)')])
                            ->from('realisasi as c')
                            ->whereColumn('a.id','c.idbagian')
                            ->where('c.kdsatker','=',$kdsatker)
                            ->where('c.tahunanggaran','=',$tahunanggaran);
                    }])
                ->where('a.idbiro','=',$idbiro)
                ->groupBy('a.id')
                ->get();

            return Datatables::of($datarealisasi)
                ->addIndexColumn()
                ->addColumn('diparevisi',function($row){
                    $diparevisi = $row->diparevisi;
                    $diparevisi = number_format($diparevisi,0,',','.');
                    return $diparevisi;

                })
                ->addColumn('realisasisakti',function($row) use ($idbiro, $kdsatker){
                    $realisasisakti = $row->realisasisakti;
                    $realisasisakti = number_format($realisasisakti,0,',','.');
                    $idbagian = $row->idbagian;
                    $linklistdata = url('/ikpa/tampildatasaktibagian/'.$idbiro.'/'.$idbagian.'/'.$kdsatker);
                    $actionBtn = '<a class="btn btn-info text-white btn-sm" href="'.$linklistdata.'">'.$realisasisakti.'</a>';
                    return $actionBtn;

                })
                ->addColumn('realisasisemar',function($row) use ($idbiro, $kdsatker){
                    $realisasisemar = $row->realisasisemar;
                    $realisasisemar = number_format($realisasisemar,0,',','.');
                    $idbagian = $row->idbagian;
                    $linklistdata = url('/ikpa/tampildatasemarbagian/'.$idbiro.'/'.$idbagian.'/'.$kdsatker);
                    $actionBtn = '<a class="btn btn-info text-white btn-sm" href="'.$linklistdata.'">'.$realisasisemar.'</a>';
                    return $actionBtn;

                })

                ->addColumn('prosentasesakti',function($row){
                    $realisasisakti = $row->realisasisakti;
                    $diparevisi = $row->diparevisi;
                    if ($diparevisi == 0){
                        $prosentase = 0;
                    }else{
                        $prosentase = ($realisasisakti/$diparevisi)*100;
                        $prosentase = number_format($prosentase,2,',','.');
                    }
                    return $prosentase;

                })

                ->addColumn('prosentasesemar',function($row){
                    $realisasisemar = $row->realisasisemar;
                    $diparevisi = $row->diparevisi;
                    if ($diparevisi == 0){
                        $prosentase = 0;
                    }else{
                        $prosentase = ($realisasisemar/$diparevisi)*100;
                        $prosentase = number_format($prosentase,2,',','.');
                    }
                    return $prosentase;
                })

                ->rawColumns(['bagian','realisasisemar','diparevisi','realisasisakti','prosentasesakti','prosentasesemar'])
                ->make(true);
        }
    }
}
