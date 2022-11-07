<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use DateTime;
use Illuminate\Http\Request;
use App\Models\AnggaranRealisasi\RefStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AdministrasiAplikasi\BearerKeyController;
use Illuminate\Support\Facades\DB;
Use Yajra\DataTables\DataTables;


class RefStatusController extends Controller{

    public function refstatus(){
        $judul = 'Data Refstatus';
        return view('AnggaranRealisasi.refstatus',[
            'judul' => $judul
        ]);
    }

    public function getRefstatusList(Request $request)
    {
        if ($request->ajax()) {
            $tahunanggaran = session('tahunanggaran');


            //ambil data refstatus
            $datarefstatus = RefStatus::where([
                ['tahunanggaran','=',$tahunanggaran],
                ['kd_sts_history','LIKE','B%']
            ])->orwhere([
                ['tahunanggaran','=',$tahunanggaran],
                ['kd_sts_history','LIKE','C%'],
                ['flag_update_coa','=',1]
            ])->get(['idrefstatus','kdsatker','kd_sts_history','jenis_revisi','revisi_ke','tgl_revisi','pagu_belanja']);
            return Datatables::of($datarefstatus)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $idrefstatus = $row->idrefstatus;
                    $statusimport = RefStatus::where('idrefstatus','=',$idrefstatus)->value('statusimport');
                    $linkimpor = url('/anggaran/importdataang/'.$idrefstatus);
                    $linkexport = url('/anggaran/exportdataang/'.$idrefstatus);
                    $linkrekap = url('/anggaran/rekapanggaran/'.$idrefstatus);
                    if ($statusimport == 2){
                        $actionBtn = '<a class="btn btn-danger text-white btn-sm" href="'.$linkimpor.'">Impor</a>
                                    <a class="btn btn-success text-white btn-sm" href="'.$linkrekap.'">Rekap</a>
                                    <a class="btn btn-success text-white btn-sm" href="'.$linkexport.'">Export</a> ';
                    }else{
                        $actionBtn = '<a class="btn btn-danger text-white btn-sm" href="'.$linkimpor.'">Impor</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }



    public function lihatdetilpagu($value, $row){
        $idrefstatus = $value;
        $linkimpor = url('/anggaran/importdataang/'.$idrefstatus);
        $linkexport = url('/anggaran/exportdataang/'.$idrefstatus);
        $linkrekap = url('/anggaran/rekapanggaran/'.$idrefstatus);
        if ($row->statusimport == 2){
            $link = url('anggaran/anggaran/'.$idrefstatus);
            return '<a class="btn btn-info text-white btn-sm" href="'.$link.'">Detil</a>
                    <a class="btn btn-danger text-white btn-sm" href="'.$linkimpor.'">Impor</a>
                    <a class="btn btn-success text-white btn-sm" href="'.$linkrekap.'">Rekap</a>
                    <a class="btn btn-success text-white btn-sm" href="'.$linkexport.'">Export</a> ';
        }else{
            return '<a class="btn btn-info text-white btn-sm" href="'.$linkimpor.'">Impor Pagu</a>';
        }

    }


    function importRefStatus(){
        //tarik data dari monsakti
        $bearerkey = new BearerKeyController();
        $bearerkey = $bearerkey->dapatkanbearerkey();

        $key = $bearerkey;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://monsakti.kemenkeu.go.id/sitp-monsakti-omspan/webservice/API/KL002/refSts/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //delete dlu refstatusnya
        DB::table('ref_status')->truncate();

        //isi lagi berdasarkan hasil tarikan data
        $hasilasli = json_decode($response);
        if(!empty($hasilasli)){
            foreach ($hasilasli as $item) {
                $ID = $item->ID;
                $KODE_KEMENTERIAN = $item->KODE_KEMENTERIAN;
                $KDSATKER = $item->KDSATKER;
                $KODE_STS_HISTORY = $item->KODE_STS_HISTORY;
                $JENIS_REVISI = $item->JENIS_REVISI;
                $REVISIKE = $item->REVISI_KE;
                $PAGU_BELANJA = $item->PAGU_BELANJA;
                $NO_DIPA = $item->NO_DIPA;
                $TGL_DIPA = $item->TGL_DIPA;
                $TGL_DIPA = new \DateTime($TGL_DIPA);
                $TGL_DIPA = $TGL_DIPA->format('Y-m-d');
                $TGL_REVISI = new \DateTime($item->TGL_REVISI);
                $TGL_REVISI = $TGL_REVISI->format('Y-m-d');
                $APPROVE = $item->APPROVE;
                $APPROVE_SPAN = $item->APPROVE_SPAN;
                $VALIDATED = $item->VALIDATED;
                $FLAG_UPDATE_COA = $item->FLAG_UPDATE_COA;
                $OWNER = $item->OWNER;
                $DIGITAL_STAMP = $item->DIGITAL_STAMP;

                $data = array(
                    'idrefstatus' => $ID,
                    'tahunanggaran' => session('tahunanggaran'),
                    'kode_kementerian' => $KODE_KEMENTERIAN,
                    'kdsatker' => $KDSATKER,
                    'kd_sts_history' => $KODE_STS_HISTORY,
                    'jenis_revisi' => $JENIS_REVISI,
                    'revisi_ke' => $REVISIKE,
                    'pagu_belanja' => $PAGU_BELANJA,
                    'no_dipa' => $NO_DIPA,
                    'tgl_dipa' => $TGL_DIPA,
                    'tgl_revisi' => $TGL_REVISI,
                    'approve' => $APPROVE,
                    'approve_span' => $APPROVE_SPAN,
                    'validated' => $VALIDATED,
                    'flag_update_coa' => $FLAG_UPDATE_COA,
                    'owner' => $OWNER,
                    'digital_stamp' => $DIGITAL_STAMP
                );
                RefStatus::insert($data);

            }
        }
        //update status import
        $statusimport = new dataAngController();
        $statusimport->updatestatusimport();

        //update waktu tarik terakhir
        $waktutarik = new DateTime();
        $waktuterakhirupdate = DB::table('timestampdata')->find(1);
        if ($waktuterakhirupdate){
            DB::table('timestampdata')->update(['updaterefstatus' => $waktutarik]);
        }else{
            DB::table('timestampdata')->insert(['updaterefstatus' => $waktutarik]);
        }
        //redirect
        return redirect()->to('anggaran/refstatus')->with('berhasil','Import Referensi Status Berhasil');
    }

}
