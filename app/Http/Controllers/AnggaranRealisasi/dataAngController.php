<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use App\Http\Controllers\AdministrasiAplikasi\BearerKeyController;
use App\Models\AnggaranRealisasi\DataAng;
use App\Models\AnggaranRealisasi\DipaRevisi;
use App\Models\AnggaranRealisasi\RefStatus;
use App\Models\AnggaranRealisasi\AnggaranBagian;
use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\TempAnggaranBagian;
use App\Models\ReferensiUnit\Bagian;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class dataAngController extends Controller
{
    function __construct(){
        ini_set('max_execution_time', 7200); //3 minutes
    }
    /**
     * Grocery CRUD Example
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */

    /**
     * Get everything we need in order to load Grocery CRUD
     *
     * @return GroceryCrud
     * @throws \GroceryCrud\Core\Exceptions\Exception
     */

    private function _getGroceryCrudEnterprise() {
        $database = $this->_getDatabaseConnection();
        $config = config('grocerycrud');

        $crud = new GroceryCrud($config, $database);

        return $crud;
    }

    /**
     * Grocery CRUD Output
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */

    private function _showOutput($judul, $lokasi, $output, $totalpagu) {
        if ($output->isJSONResponse) {
            return response($output->output, 200)
                ->header('Content-Type', 'application/json')
                ->header('charset', 'utf-8');
        }

        $css_files = $output->css_files;
        $js_files = $output->js_files;
        $output = $output->output;

        return view($lokasi, [
            'output' => $output,
            'css_files' => $css_files,
            'js_files' => $js_files,
            'judul' => $judul,
            'totalpagu' => $totalpagu
        ]);
    }

    /**
     * Get database credentials as a Zend Db Adapter configuration
     * @return array[]
     */
    private function _getDatabaseConnection() {

        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql',
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8'
            ]
        ];
    }

    public function tampilanggaran($idrefstatus)
    {

        $totalpagu = DataAng::where([
            ['header1','=',0],
            ['header2','=',0],
            ['idrefstatus','=',$idrefstatus]
        ])->sum('total');
        $totalpagu = number_format($totalpagu,0,',','.');
        $lokasi = 'AnggaranRealisasi.anggaran';
        $judul = 'Daftar Pagu';
        $crud = $this->_getGroceryCrudEnterprise();
        $where = 'nomoritem != 0 and idrefstatus ='.$idrefstatus;
        $crud->where($where);
        $crud->setTable('data_ang');
        $crud->setSubject('Anggaran', 'Anggaran');
        $crud->columns(['kdsatker','kodeprogram','kodekegiatan','kodeoutput','volumeoutput','kodesuboutput','volumesuboutput','kodekomponen','kodesubkomponen','uraiansubkomponen','kodeakun','total']);
        $crud->displayAs('kodeprogram','Program');
        $crud->displayAs('kodekegiatan','Kegiatan');
        $crud->displayAs('kodeoutput','Output');
        $crud->displayAs('volumeoutput','Volume Output');
        $crud->displayAs('kodesuboutput','Sub Output');
        $crud->displayAs('volumesuboutput','Volume Suboutput');
        $crud->displayAs('kodekomponen','Komponen');
        $crud->displayAs('kodesubkomponen','SubKomponen');
        $crud->displayAs('uraiansubkomponen','Uraian Subkomponen');
        $crud->displayAs('kodeakun','Akun');
        $crud->displayAs('total','Total');

        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output, $totalpagu);
    }

    function importseluruhdata(){
        $tahunanggaran = session('tahunanggaran');
        $where = array(
            'jenis_revisi' => 'DIPA_REVISI',
            'tahunanggaran' => $tahunanggaran
        );

        $orwhere = array(
            'jenis_revisi' => 'SATKER_REVISI',
            'tahunanggaran' => $tahunanggaran
        );
        $datarefstatus = RefStatus::where($where)->orwhere($orwhere)->get();
        foreach ($datarefstatus as $drs){
            $idrefstatus = $drs->idrefstatus;

            //cek apakah sudah ada di data anggaran
            $adadata = DataAng::where('idrefstatus','=',$idrefstatus)->get()->count();
            if ($adadata<1){
                $this->importdatatanparedirect($idrefstatus);
            }
        }
        return redirect('/anggaran/refstatus')->with('berhasil', 'Data Anggaran Berhasil Diimpor');
    }

    function importdatatanparedirect($idrefstatus){
        $tahunanggaran = session('tahunanggaran');
        $datarefstatus = RefStatus::where('idrefstatus', $idrefstatus)->get();
        foreach ($datarefstatus as $item) {
            $kdsatker = $item->kdsatker;
            $kd_sts_history = $item->kd_sts_history;
        }

        $bearerkey = new BearerKeyController();
        $bearerkey = $bearerkey->dapatkanbearerkey();
        $key = $bearerkey;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://monsakti.kemenkeu.go.id/sitp-monsakti-omspan/webservice/API/KL002/dataAng/' . $kdsatker . '/' . $kd_sts_history,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $diolah = substr(json_encode($response), 10, 14);
        if ($diolah !== "" && $diolah !== "<b>Fatal error") {
            $hasilasli = json_decode($response);
            $where = array(
                'idrefstatus' => $idrefstatus
            );
            $adadata = DataAng::where($where)->get()->count();
            if ($adadata > 0) {
                DataAng::where($where)->delete();
                foreach ($hasilasli as $item) {
                    $KDSATKER = $item->KDSATKER;
                    $KODE_PROGRAM = $item->KODE_PROGRAM;
                    $KODE_KEGIATAN = $item->KODE_KEGIATAN;
                    $KODE_OUTPUT = $item->KODE_OUTPUT;
                    $KDIB = $item->KDIB;
                    $VOLUME_OUTPUT = $item->VOLUME_OUTPUT;
                    $KODE_SUBOUTPUT = $item->KODE_SUBOUTPUT;
                    $VOLUME_SUBOUTPUT = $item->VOLUME_SUBOUTPUT;
                    $KODE_KOMPONEN = $item->KODE_KOMPONEN;
                    $KODE_SUBKOMPONEN = $item->KODE_SUBKOMPONEN;
                    $URAIAN_SUBKOMPONEN = $item->URAIAN_SUBKOMPONEN;
                    $KODE_AKUN = $item->KODE_AKUN;
                    $KODE_JENIS_BEBAN = $item->KODE_JENIS_BEBAN;
                    $KODE_CARA_TARIK = $item->KODE_CARA_TARIK;
                    $HEADER1 = $item->HEADER1;
                    $HEADER2 = $item->HEADER2;
                    $KODE_ITEM = $item->KODE_ITEM;
                    $NOMOR_ITEM = $item->NOMOR_ITEM;
                    $URAIAN_ITEM = $item->URAIAN_ITEM;
                    $SUMBER_DANA = $item->SUMBER_DANA;
                    $VOL_KEG_1 = $item->VOL_KEG_1;
                    $VOL_KEG_1 = (int)$VOL_KEG_1;
                    $SAT_KEG_1 = $item->SAT_KEG_1;
                    $VOL_KEG_2 = $item->VOL_KEG_2;
                    $VOL_KEG_2 = (int)$VOL_KEG_2;
                    $SAT_KEG_2 = $item->SAT_KEG_2;
                    $VOL_KEG_3 = $item->VOL_KEG_3;
                    $VOL_KEG_3 = (int)$VOL_KEG_3;
                    $SAT_KEG_3 = $item->SAT_KEG_3;
                    $VOL_KEG_4 = (int)$item->VOL_KEG_4;
                    $SAT_KEG_4 = $item->SAT_KEG_4;
                    $VOLKEG = (int)$item->VOLKEG;
                    $SATKEG = $item->SATKEG;
                    $HARGASAT = (int)$item->HARGASAT;
                    $TOTAL = (int)$item->TOTAL;
                    $KODE_BLOKIR = $item->KODE_BLOKIR;
                    $NILAI_BLOKIR = (int)$item->NILAI_BLOKIR;
                    $KODE_STS_HISTORY = $item->KODE_STS_HISTORY;
                    $POK_NILAI_1 = (int)$item->POK_NILAI_1;
                    $POK_NILAI_2 = (int)$item->POK_NILAI_2;
                    $POK_NILAI_3 = (int)$item->POK_NILAI_3;
                    $POK_NILAI_4 = (int)$item->POK_NILAI_4;
                    $POK_NILAI_5 = (int)$item->POK_NILAI_5;
                    $POK_NILAI_6 = (int)$item->POK_NILAI_6;
                    $POK_NILAI_7 = (int)$item->POK_NILAI_7;
                    $POK_NILAI_8 = (int)$item->POK_NILAI_8;
                    $POK_NILAI_9 = (int)$item->POK_NILAI_9;
                    $POK_NILAI_10 = (int)$item->POK_NILAI_10;
                    $POK_NILAI_11 = (int)$item->POK_NILAI_11;
                    $POK_NILAI_12 = (int)$item->POK_NILAI_12;

                    $data = array(
                        'tahunanggaran' => $tahunanggaran,
                        'idrefstatus' => $idrefstatus,
                        'kdsatker' => $KDSATKER,
                        'kodeprogram' => $KODE_PROGRAM,
                        'kodekegiatan' => $KODE_KEGIATAN,
                        'kodeoutput' => $KODE_OUTPUT,
                        'kdib' => $KDIB,
                        'volumeoutput' => $VOLUME_OUTPUT,
                        'kodesuboutput' => $KODE_SUBOUTPUT,
                        'volumesuboutput' => $VOLUME_SUBOUTPUT,
                        'kodekomponen' => $KODE_KOMPONEN,
                        'kodesubkomponen' => $KODE_SUBKOMPONEN,
                        'uraiansubkomponen' => $URAIAN_SUBKOMPONEN,
                        'kodeakun' => $KODE_AKUN,
                        'pengenal' => $KODE_PROGRAM.'.'.$KODE_KEGIATAN.'.'.$KODE_OUTPUT.'.'.$KODE_SUBOUTPUT.'.'.$KODE_KOMPONEN.'.'.$KODE_SUBKOMPONEN.'.'.$KODE_AKUN,
                        'kodejenisbeban' => $KODE_JENIS_BEBAN,
                        'kodecaratarik' => $KODE_CARA_TARIK,
                        'header1' => $HEADER1,
                        'header2' => $HEADER2,
                        'kodeitem' => $KODE_ITEM,
                        'nomoritem' => $NOMOR_ITEM,
                        'uraianitem' => $URAIAN_ITEM,
                        'sumberdana' => $SUMBER_DANA,
                        'volkeg1' => $VOL_KEG_1,
                        'satkeg1' => $SAT_KEG_1,
                        'volkeg2' => $VOL_KEG_2,
                        'satkeg2' => $SAT_KEG_2,
                        'volkeg3' => $VOL_KEG_3,
                        'satkeg3' => $SAT_KEG_3,
                        'volkeg4' => $VOL_KEG_4,
                        'satkeg4' => $SAT_KEG_4,
                        'volkeg' => $VOLKEG,
                        'satkeg' => $SATKEG,
                        'hargasat' => $HARGASAT,
                        'total' => $TOTAL,
                        'kodeblokir' => $KODE_BLOKIR,
                        'nilaiblokir' => $NILAI_BLOKIR,
                        'kodestshistory' => $KODE_STS_HISTORY,
                        'poknilai1' => $POK_NILAI_1,
                        'poknilai2' => $POK_NILAI_2,
                        'poknilai3' => $POK_NILAI_3,
                        'poknilai4' => $POK_NILAI_4,
                        'poknilai5' => $POK_NILAI_5,
                        'poknilai6' => $POK_NILAI_6,
                        'poknilai7' => $POK_NILAI_7,
                        'poknilai8' => $POK_NILAI_8,
                        'poknilai9' => $POK_NILAI_9,
                        'poknilai10' => $POK_NILAI_10,
                        'poknilai11' => $POK_NILAI_11,
                        'poknilai12' => $POK_NILAI_12
                    );
                    DataAng::insert($data);
                }
            } else {
                foreach ($hasilasli as $item) {
                    $KDSATKER = $item->KDSATKER;
                    $KODE_PROGRAM = $item->KODE_PROGRAM;
                    $KODE_KEGIATAN = $item->KODE_KEGIATAN;
                    $KODE_OUTPUT = $item->KODE_OUTPUT;
                    $KDIB = $item->KDIB;
                    $VOLUME_OUTPUT = $item->VOLUME_OUTPUT;
                    $KODE_SUBOUTPUT = $item->KODE_SUBOUTPUT;
                    $VOLUME_SUBOUTPUT = $item->VOLUME_SUBOUTPUT;
                    $KODE_KOMPONEN = $item->KODE_KOMPONEN;
                    $KODE_SUBKOMPONEN = $item->KODE_SUBKOMPONEN;
                    $URAIAN_SUBKOMPONEN = $item->URAIAN_SUBKOMPONEN;
                    $KODE_AKUN = $item->KODE_AKUN;
                    $KODE_JENIS_BEBAN = $item->KODE_JENIS_BEBAN;
                    $KODE_CARA_TARIK = $item->KODE_CARA_TARIK;
                    $HEADER1 = $item->HEADER1;
                    $HEADER2 = $item->HEADER2;
                    $KODE_ITEM = $item->KODE_ITEM;
                    $NOMOR_ITEM = $item->NOMOR_ITEM;
                    $URAIAN_ITEM = $item->URAIAN_ITEM;
                    $SUMBER_DANA = $item->SUMBER_DANA;
                    $VOL_KEG_1 = $item->VOL_KEG_1;
                    $VOL_KEG_1 = (int)$VOL_KEG_1;
                    $SAT_KEG_1 = $item->SAT_KEG_1;
                    $VOL_KEG_2 = $item->VOL_KEG_2;
                    $VOL_KEG_2 = (int)$VOL_KEG_2;
                    $SAT_KEG_2 = $item->SAT_KEG_2;
                    $VOL_KEG_3 = $item->VOL_KEG_3;
                    $VOL_KEG_3 = (int)$VOL_KEG_3;
                    $SAT_KEG_3 = $item->SAT_KEG_3;
                    $VOL_KEG_4 = (int)$item->VOL_KEG_4;
                    $SAT_KEG_4 = $item->SAT_KEG_4;
                    $VOLKEG = (int)$item->VOLKEG;
                    $SATKEG = $item->SATKEG;
                    $HARGASAT = (int)$item->HARGASAT;
                    $TOTAL = (int)$item->TOTAL;
                    $KODE_BLOKIR = $item->KODE_BLOKIR;
                    $NILAI_BLOKIR = (int)$item->NILAI_BLOKIR;
                    $KODE_STS_HISTORY = $item->KODE_STS_HISTORY;
                    $POK_NILAI_1 = (int)$item->POK_NILAI_1;
                    $POK_NILAI_2 = (int)$item->POK_NILAI_2;
                    $POK_NILAI_3 = (int)$item->POK_NILAI_3;
                    $POK_NILAI_4 = (int)$item->POK_NILAI_4;
                    $POK_NILAI_5 = (int)$item->POK_NILAI_5;
                    $POK_NILAI_6 = (int)$item->POK_NILAI_6;
                    $POK_NILAI_7 = (int)$item->POK_NILAI_7;
                    $POK_NILAI_8 = (int)$item->POK_NILAI_8;
                    $POK_NILAI_9 = (int)$item->POK_NILAI_9;
                    $POK_NILAI_10 = (int)$item->POK_NILAI_10;
                    $POK_NILAI_11 = (int)$item->POK_NILAI_11;
                    $POK_NILAI_12 = (int)$item->POK_NILAI_12;

                    $data = array(
                        'tahunanggaran' => $tahunanggaran,
                        'idrefstatus' => $idrefstatus,
                        'kdsatker' => $KDSATKER,
                        'kodeprogram' => $KODE_PROGRAM,
                        'kodekegiatan' => $KODE_KEGIATAN,
                        'kodeoutput' => $KODE_OUTPUT,
                        'kdib' => $KDIB,
                        'volumeoutput' => $VOLUME_OUTPUT,
                        'kodesuboutput' => $KODE_SUBOUTPUT,
                        'volumesuboutput' => $VOLUME_SUBOUTPUT,
                        'kodekomponen' => $KODE_KOMPONEN,
                        'kodesubkomponen' => $KODE_SUBKOMPONEN,
                        'uraiansubkomponen' => $URAIAN_SUBKOMPONEN,
                        'kodeakun' => $KODE_AKUN,
                        'pengenal' => $KODE_PROGRAM.'.'.$KODE_KEGIATAN.'.'.$KODE_OUTPUT.'.'.$KODE_SUBOUTPUT.'.'.$KODE_KOMPONEN.'.'.$KODE_SUBKOMPONEN.'.'.$KODE_AKUN,
                        'kodejenisbeban' => $KODE_JENIS_BEBAN,
                        'kodecaratarik' => $KODE_CARA_TARIK,
                        'header1' => $HEADER1,
                        'header2' => $HEADER2,
                        'kodeitem' => $KODE_ITEM,
                        'nomoritem' => $NOMOR_ITEM,
                        'uraianitem' => $URAIAN_ITEM,
                        'sumberdana' => $SUMBER_DANA,
                        'volkeg1' => $VOL_KEG_1,
                        'satkeg1' => $SAT_KEG_1,
                        'volkeg2' => $VOL_KEG_2,
                        'satkeg2' => $SAT_KEG_2,
                        'volkeg3' => $VOL_KEG_3,
                        'satkeg3' => $SAT_KEG_3,
                        'volkeg4' => $VOL_KEG_4,
                        'satkeg4' => $SAT_KEG_4,
                        'volkeg' => $VOLKEG,
                        'satkeg' => $SATKEG,
                        'hargasat' => $HARGASAT,
                        'total' => $TOTAL,
                        'kodeblokir' => $KODE_BLOKIR,
                        'nilaiblokir' => $NILAI_BLOKIR,
                        'kodestshistory' => $KODE_STS_HISTORY,
                        'poknilai1' => $POK_NILAI_1,
                        'poknilai2' => $POK_NILAI_2,
                        'poknilai3' => $POK_NILAI_3,
                        'poknilai4' => $POK_NILAI_4,
                        'poknilai5' => $POK_NILAI_5,
                        'poknilai6' => $POK_NILAI_6,
                        'poknilai7' => $POK_NILAI_7,
                        'poknilai8' => $POK_NILAI_8,
                        'poknilai9' => $POK_NILAI_9,
                        'poknilai10' => $POK_NILAI_10,
                        'poknilai11' => $POK_NILAI_11,
                        'poknilai12' => $POK_NILAI_12
                    );
                    DataAng::insert($data);

                }
            }
            //rubah status importnya
            $dataupdate = array(
                'statusimport' => 2
            );
            RefStatus::where($where)->update($dataupdate);
            $this->rekapanggaran($idrefstatus);

        }
    }

    function importdataang($idrefstatus)
    {
        $tahunanggaran = session('tahunanggaran');
        $datarefstatus = RefStatus::where('idrefstatus','=',$idrefstatus)->get();
        foreach ($datarefstatus as $item) {
            $kdsatker = $item->kdsatker;
            $kd_sts_history = $item->kd_sts_history;
        }

        $bearerkey = new BearerKeyController();
        $bearerkey = $bearerkey->dapatkanbearerkey();
        $key = $bearerkey;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://monsakti.kemenkeu.go.id/sitp-monsakti-omspan/webservice/API/KL002/dataAng/' . $kdsatker . '/' . $kd_sts_history,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $diolah = substr(json_encode($response), 10, 14);
        if ($diolah !== "" && $diolah !== "<b>Fatal error") {
            $hasilasli = json_decode($response);
            $where = array(
                'idrefstatus' => $idrefstatus
            );
            $adadata = DataAng::where($where)->get()->count();
            if ($adadata > 0) {
                DataAng::where($where)->delete();
                foreach ($hasilasli as $item) {
                    $KDSATKER = $item->KDSATKER;
                    $KODE_PROGRAM = $item->KODE_PROGRAM;
                    $KODE_KEGIATAN = $item->KODE_KEGIATAN;
                    $KODE_OUTPUT = $item->KODE_OUTPUT;
                    $KDIB = $item->KDIB;
                    $VOLUME_OUTPUT = $item->VOLUME_OUTPUT;
                    $KODE_SUBOUTPUT = $item->KODE_SUBOUTPUT;
                    $VOLUME_SUBOUTPUT = $item->VOLUME_SUBOUTPUT;
                    $KODE_KOMPONEN = $item->KODE_KOMPONEN;
                    $KODE_SUBKOMPONEN = $item->KODE_SUBKOMPONEN;
                    $URAIAN_SUBKOMPONEN = $item->URAIAN_SUBKOMPONEN;
                    $KODE_AKUN = $item->KODE_AKUN;
                    $KODE_JENIS_BEBAN = $item->KODE_JENIS_BEBAN;
                    $KODE_CARA_TARIK = $item->KODE_CARA_TARIK;
                    $HEADER1 = $item->HEADER1;
                    $HEADER2 = $item->HEADER2;
                    $KODE_ITEM = $item->KODE_ITEM;
                    $NOMOR_ITEM = $item->NOMOR_ITEM;
                    $URAIAN_ITEM = $item->URAIAN_ITEM;
                    $SUMBER_DANA = $item->SUMBER_DANA;
                    $VOL_KEG_1 = $item->VOL_KEG_1;
                    $VOL_KEG_1 = (int)$VOL_KEG_1;
                    $SAT_KEG_1 = $item->SAT_KEG_1;
                    $VOL_KEG_2 = $item->VOL_KEG_2;
                    $VOL_KEG_2 = (int)$VOL_KEG_2;
                    $SAT_KEG_2 = $item->SAT_KEG_2;
                    $VOL_KEG_3 = $item->VOL_KEG_3;
                    $VOL_KEG_3 = (int)$VOL_KEG_3;
                    $SAT_KEG_3 = $item->SAT_KEG_3;
                    $VOL_KEG_4 = (int)$item->VOL_KEG_4;
                    $SAT_KEG_4 = $item->SAT_KEG_4;
                    $VOLKEG = (int)$item->VOLKEG;
                    $SATKEG = $item->SATKEG;
                    $HARGASAT = (int)$item->HARGASAT;
                    $TOTAL = (int)$item->TOTAL;
                    $KODE_BLOKIR = $item->KODE_BLOKIR;
                    $NILAI_BLOKIR = (int)$item->NILAI_BLOKIR;
                    $KODE_STS_HISTORY = $item->KODE_STS_HISTORY;
                    $POK_NILAI_1 = (int)$item->POK_NILAI_1;
                    $POK_NILAI_2 = (int)$item->POK_NILAI_2;
                    $POK_NILAI_3 = (int)$item->POK_NILAI_3;
                    $POK_NILAI_4 = (int)$item->POK_NILAI_4;
                    $POK_NILAI_5 = (int)$item->POK_NILAI_5;
                    $POK_NILAI_6 = (int)$item->POK_NILAI_6;
                    $POK_NILAI_7 = (int)$item->POK_NILAI_7;
                    $POK_NILAI_8 = (int)$item->POK_NILAI_8;
                    $POK_NILAI_9 = (int)$item->POK_NILAI_9;
                    $POK_NILAI_10 = (int)$item->POK_NILAI_10;
                    $POK_NILAI_11 = (int)$item->POK_NILAI_11;
                    $POK_NILAI_12 = (int)$item->POK_NILAI_12;

                    $data = array(
                        'tahunanggaran' => $tahunanggaran,
                        'idrefstatus' => $idrefstatus,
                        'kdsatker' => $KDSATKER,
                        'kodeprogram' => $KODE_PROGRAM,
                        'kodekegiatan' => $KODE_KEGIATAN,
                        'kodeoutput' => $KODE_OUTPUT,
                        'kdib' => $KDIB,
                        'volumeoutput' => $VOLUME_OUTPUT,
                        'kodesuboutput' => $KODE_SUBOUTPUT,
                        'volumesuboutput' => $VOLUME_SUBOUTPUT,
                        'kodekomponen' => $KODE_KOMPONEN,
                        'kodesubkomponen' => $KODE_SUBKOMPONEN,
                        'uraiansubkomponen' => $URAIAN_SUBKOMPONEN,
                        'kodeakun' => $KODE_AKUN,
                        'pengenal' => $KODE_PROGRAM.'.'.$KODE_KEGIATAN.'.'.$KODE_OUTPUT.'.'.$KODE_SUBOUTPUT.'.'.$KODE_KOMPONEN.'.'.$KODE_SUBKOMPONEN.'.'.$KODE_AKUN,
                        'kodejenisbeban' => $KODE_JENIS_BEBAN,
                        'kodecaratarik' => $KODE_CARA_TARIK,
                        'header1' => $HEADER1,
                        'header2' => $HEADER2,
                        'kodeitem' => $KODE_ITEM,
                        'nomoritem' => $NOMOR_ITEM,
                        'uraianitem' => $URAIAN_ITEM,
                        'sumberdana' => $SUMBER_DANA,
                        'volkeg1' => $VOL_KEG_1,
                        'satkeg1' => $SAT_KEG_1,
                        'volkeg2' => $VOL_KEG_2,
                        'satkeg2' => $SAT_KEG_2,
                        'volkeg3' => $VOL_KEG_3,
                        'satkeg3' => $SAT_KEG_3,
                        'volkeg4' => $VOL_KEG_4,
                        'satkeg4' => $SAT_KEG_4,
                        'volkeg' => $VOLKEG,
                        'satkeg' => $SATKEG,
                        'hargasat' => $HARGASAT,
                        'total' => $TOTAL,
                        'kodeblokir' => $KODE_BLOKIR,
                        'nilaiblokir' => $NILAI_BLOKIR,
                        'kodestshistory' => $KODE_STS_HISTORY,
                        'poknilai1' => $POK_NILAI_1,
                        'poknilai2' => $POK_NILAI_2,
                        'poknilai3' => $POK_NILAI_3,
                        'poknilai4' => $POK_NILAI_4,
                        'poknilai5' => $POK_NILAI_5,
                        'poknilai6' => $POK_NILAI_6,
                        'poknilai7' => $POK_NILAI_7,
                        'poknilai8' => $POK_NILAI_8,
                        'poknilai9' => $POK_NILAI_9,
                        'poknilai10' => $POK_NILAI_10,
                        'poknilai11' => $POK_NILAI_11,
                        'poknilai12' => $POK_NILAI_12
                    );
                    DataAng::insert($data);
                }
            } else {
                foreach ($hasilasli as $item) {
                    $KDSATKER = $item->KDSATKER;
                    $KODE_PROGRAM = $item->KODE_PROGRAM;
                    $KODE_KEGIATAN = $item->KODE_KEGIATAN;
                    $KODE_OUTPUT = $item->KODE_OUTPUT;
                    $KDIB = $item->KDIB;
                    $VOLUME_OUTPUT = $item->VOLUME_OUTPUT;
                    $KODE_SUBOUTPUT = $item->KODE_SUBOUTPUT;
                    $VOLUME_SUBOUTPUT = $item->VOLUME_SUBOUTPUT;
                    $KODE_KOMPONEN = $item->KODE_KOMPONEN;
                    $KODE_SUBKOMPONEN = $item->KODE_SUBKOMPONEN;
                    $URAIAN_SUBKOMPONEN = $item->URAIAN_SUBKOMPONEN;
                    $KODE_AKUN = $item->KODE_AKUN;
                    $KODE_JENIS_BEBAN = $item->KODE_JENIS_BEBAN;
                    $KODE_CARA_TARIK = $item->KODE_CARA_TARIK;
                    $HEADER1 = $item->HEADER1;
                    $HEADER2 = $item->HEADER2;
                    $KODE_ITEM = $item->KODE_ITEM;
                    $NOMOR_ITEM = $item->NOMOR_ITEM;
                    $URAIAN_ITEM = $item->URAIAN_ITEM;
                    $SUMBER_DANA = $item->SUMBER_DANA;
                    $VOL_KEG_1 = $item->VOL_KEG_1;
                    $VOL_KEG_1 = (int)$VOL_KEG_1;
                    $SAT_KEG_1 = $item->SAT_KEG_1;
                    $VOL_KEG_2 = $item->VOL_KEG_2;
                    $VOL_KEG_2 = (int)$VOL_KEG_2;
                    $SAT_KEG_2 = $item->SAT_KEG_2;
                    $VOL_KEG_3 = $item->VOL_KEG_3;
                    $VOL_KEG_3 = (int)$VOL_KEG_3;
                    $SAT_KEG_3 = $item->SAT_KEG_3;
                    $VOL_KEG_4 = (int)$item->VOL_KEG_4;
                    $SAT_KEG_4 = $item->SAT_KEG_4;
                    $VOLKEG = (int)$item->VOLKEG;
                    $SATKEG = $item->SATKEG;
                    $HARGASAT = (int)$item->HARGASAT;
                    $TOTAL = (int)$item->TOTAL;
                    $KODE_BLOKIR = $item->KODE_BLOKIR;
                    $NILAI_BLOKIR = (int)$item->NILAI_BLOKIR;
                    $KODE_STS_HISTORY = $item->KODE_STS_HISTORY;
                    $POK_NILAI_1 = (int)$item->POK_NILAI_1;
                    $POK_NILAI_2 = (int)$item->POK_NILAI_2;
                    $POK_NILAI_3 = (int)$item->POK_NILAI_3;
                    $POK_NILAI_4 = (int)$item->POK_NILAI_4;
                    $POK_NILAI_5 = (int)$item->POK_NILAI_5;
                    $POK_NILAI_6 = (int)$item->POK_NILAI_6;
                    $POK_NILAI_7 = (int)$item->POK_NILAI_7;
                    $POK_NILAI_8 = (int)$item->POK_NILAI_8;
                    $POK_NILAI_9 = (int)$item->POK_NILAI_9;
                    $POK_NILAI_10 = (int)$item->POK_NILAI_10;
                    $POK_NILAI_11 = (int)$item->POK_NILAI_11;
                    $POK_NILAI_12 = (int)$item->POK_NILAI_12;

                    $data = array(
                        'tahunanggaran' => $tahunanggaran,
                        'idrefstatus' => $idrefstatus,
                        'kdsatker' => $KDSATKER,
                        'kodeprogram' => $KODE_PROGRAM,
                        'kodekegiatan' => $KODE_KEGIATAN,
                        'kodeoutput' => $KODE_OUTPUT,
                        'kdib' => $KDIB,
                        'volumeoutput' => $VOLUME_OUTPUT,
                        'kodesuboutput' => $KODE_SUBOUTPUT,
                        'volumesuboutput' => $VOLUME_SUBOUTPUT,
                        'kodekomponen' => $KODE_KOMPONEN,
                        'kodesubkomponen' => $KODE_SUBKOMPONEN,
                        'uraiansubkomponen' => $URAIAN_SUBKOMPONEN,
                        'kodeakun' => $KODE_AKUN,
                        'pengenal' => $KODE_PROGRAM.'.'.$KODE_KEGIATAN.'.'.$KODE_OUTPUT.'.'.$KODE_SUBOUTPUT.'.'.$KODE_KOMPONEN.'.'.$KODE_SUBKOMPONEN.'.'.$KODE_AKUN,
                        'kodejenisbeban' => $KODE_JENIS_BEBAN,
                        'kodecaratarik' => $KODE_CARA_TARIK,
                        'header1' => $HEADER1,
                        'header2' => $HEADER2,
                        'kodeitem' => $KODE_ITEM,
                        'nomoritem' => $NOMOR_ITEM,
                        'uraianitem' => $URAIAN_ITEM,
                        'sumberdana' => $SUMBER_DANA,
                        'volkeg1' => $VOL_KEG_1,
                        'satkeg1' => $SAT_KEG_1,
                        'volkeg2' => $VOL_KEG_2,
                        'satkeg2' => $SAT_KEG_2,
                        'volkeg3' => $VOL_KEG_3,
                        'satkeg3' => $SAT_KEG_3,
                        'volkeg4' => $VOL_KEG_4,
                        'satkeg4' => $SAT_KEG_4,
                        'volkeg' => $VOLKEG,
                        'satkeg' => $SATKEG,
                        'hargasat' => $HARGASAT,
                        'total' => $TOTAL,
                        'kodeblokir' => $KODE_BLOKIR,
                        'nilaiblokir' => $NILAI_BLOKIR,
                        'kodestshistory' => $KODE_STS_HISTORY,
                        'poknilai1' => $POK_NILAI_1,
                        'poknilai2' => $POK_NILAI_2,
                        'poknilai3' => $POK_NILAI_3,
                        'poknilai4' => $POK_NILAI_4,
                        'poknilai5' => $POK_NILAI_5,
                        'poknilai6' => $POK_NILAI_6,
                        'poknilai7' => $POK_NILAI_7,
                        'poknilai8' => $POK_NILAI_8,
                        'poknilai9' => $POK_NILAI_9,
                        'poknilai10' => $POK_NILAI_10,
                        'poknilai11' => $POK_NILAI_11,
                        'poknilai12' => $POK_NILAI_12
                    );
                    DataAng::insert($data);

                }
            }
            //rekap anggaran agar bisa dibagi perbagian
            $this->rekapanggarannoredirect($idrefstatus);

            //rubah status importnya
            $dataupdate = array(
                'statusimport' => 2
            );
            RefStatus::where('idrefstatus','=',$idrefstatus)->update($dataupdate);
            //redirect
            return redirect('/anggaran/refstatus')->with('berhasil', 'Data Anggaran Berhasil Diimpor');
        }
        else{
            return redirect('/anggaran/refstatus')->with('gagal', 'Data Anggaran Gagal Diimpor, Data Terlalu Besar');
        }


    }
    /*
    public function rekapanggaranseluruh(){
        $tahunanggaran = session('tahunanggaran');
        $datarefstatus = RefStatus::where([
            ['tahunanggaran','=',$tahunanggaran],
            ['kd_sts_history','LIKE','B%']
        ])->orwhere([
            ['tahunanggaran','=',$tahunanggaran],
            ['kd_sts_history','LIKE','C%'],
            ['flag_update_coa','=',1],
        ])->get();
        foreach ($datarefstatus as $d){
            $idrefstatus = $d->idrefstatus;
            $dataanggaran = DataAng::where('idrefstatus','=',$idrefstatus)->get();
            foreach ($dataanggaran as $p){
                $pengenal = $p->pengenal;
                $kodeprogram = $p->kodeprogram;
                $kodekegiatan = $p->kodekegiatan;
                $kodeoutput = $p->kodeoutput;
                $kodesuboutput = $p->kodesuboutput;
                $kodekomponen = $p->kodekomponen;
                $kodesubkomponen = $p->kodesubkomponen;
                $kodeakun = $p->kodeakun;
                $idrefstatus = $p->idrefstatus;
                //cek apakah sudah ada di tabel
                $adadata = AnggaranBagian::where('pengenal','=',$pengenal)->get()->count();

                if($adadata < 1){
                    $datainsert = array(
                        'tahunanggaran' => $tahunanggaran,
                        'kodeprogram' => $kodeprogram,
                        'kodekegiatan' => $kodekegiatan,
                        'kodeoutput' => $kodeoutput,
                        'kodesuboutput' => $kodesuboutput,
                        'kodekomponen' => $kodekomponen,
                        'kodesubkomponen' => $kodesubkomponen,
                        'kodeakun' => $kodeakun,
                        'pengenal' => $pengenal,
                        'idrefstatus' => $idrefstatus,
                        'idbagian' => null
                    );
                    AnggaranBagian::insert($datainsert);
                }
            }
        }
        return redirect()->to('anggaran/refstatus')->with('rekapberhasil','Rekap Seluruh Anggaran Berhasil');
    }
    */

    public function rekapanggarannoredirect($idrefstatus){
        $tahunanggaran = session('tahunanggaran');
        $datapagu = DataAng::where([
            ['header1','=',0],
            ['header2','=',0],
            ['idrefstatus','=',$idrefstatus]
        ])->get();

        foreach ($datapagu as $item){
            $kdsatker = $item->kdsatker;
            $kodeprogram = $item->kodeprogram;
            $kodekegiatan = $item->kodekegiatan;
            $kodeoutput = $item->kodeoutput;
            $kodesubout = $item->kodesuboutput;
            $kodekomponen = $item->kodekomponen;
            $kodesubkomponen = $item->kodesubkomponen;
            $kodeakun = $item->kodeakun;
            $pengenal = $kodeprogram.'.'.$kodekegiatan.'.'.$kodeoutput.'.'.$kodesubout.'.'.$kodekomponen.'.'.$kodesubkomponen.'.'.$kodeakun;

            $where = array(
                'tahunanggaran' => $tahunanggaran,
                'pengenal' => $pengenal
            );

            $adadata = AnggaranBagian::where($where)->get()->count();
            if ($adadata == 0){
                $data = array(
                    'tahunanggaran' => $tahunanggaran,
                    'kdsatker' => $kdsatker,
                    'kodeprogram' => $kodeprogram,
                    'kodekegiatan' => $kodekegiatan,
                    'kodeoutput' => $kodeoutput,
                    'kodesuboutput' => $kodesubout,
                    'kodekomponen' => $kodekomponen,
                    'kodesubkomponen' => $kodesubkomponen,
                    'kodeakun' => $kodeakun,
                    'pengenal' => $pengenal,
                    'idrefstatus' => $idrefstatus,
                    'idbagian' => null
                );
                AnggaranBagian::insert($data);
            }
        }
    }

    public function rekapanggaran($idrefstatus){
        $this->rekapanggarannoredirect($idrefstatus);
        $this->summarydipa($idrefstatus);
        return redirect()->to('anggaran/anggaranbagian')->with('rekapberhasil','Rekap Anggaran Bagian Berhasil');
    }

    public function alokasiidbagiankeanggaran(){
        $tahunanggaran = session('tahunanggaran');
        $dataanggaranbagian = AnggaranBagian::where([
            ['tahunanggaran','=',$tahunanggaran]
        ])->get();

        foreach ($dataanggaranbagian as $item){
            $pengenal = $item->pengenal;
            $id = $item->id;

            //dapatkan id bagian =
            $datatempanggaranbagian = TempAnggaranBagian::where('pengenal','=',$pengenal)->get()->first();
            if ($datatempanggaranbagian){
                $idbagian = $datatempanggaranbagian->idbagian;

                //update data di anggaran bagian
                $dataupdate = array(
                    'idbagian' => $idbagian
                );
                AnggaranBagian::where('id','=',$id)->update($dataupdate);
            }

        }
    }

    public function updatestatusimport(){
        $tahunanggaran = session('tahunanggaran');
        $datarefstatus = RefStatus::where([
            ['tahunanggaran','=',$tahunanggaran],
            ['kd_sts_history','LIKE','B%']
        ])->orwhere([
            ['kd_sts_history','LIKE','C%'],
            ['tahunanggaran','=',$tahunanggaran],
            ['flag_update_coa','=',1]
        ])->get();

        foreach ($datarefstatus as $drs){
            $idrefstatus = $drs->idrefstatus;
            //cek di data anggaran
            $adadataanggaran = DataAng::where('idrefstatus','=',$idrefstatus)->count();

            if ($adadataanggaran >0){
                RefStatus::where('idrefstatus','=',$idrefstatus)->update([
                   'statusimport' => 2
                ]);
            }

        }
    }

    public function exportdataanggaran($idrefstatus){
        $datarefstatus = RefStatus::where('idrefstatus', $idrefstatus)->get();
        foreach ($datarefstatus as $item) {
            $kdsatker = $item->kdsatker;
            $kd_sts_history = $item->kd_sts_history;
            $nodipa = $item->no_dipa;
            $tgl_dipa = $item->tgl_dipa;
            $revisike = $item->revisi_ke;
            $tgl_revisi = $item->tgl_revisi;
            $kodesatker = $item->kdsatker;
        }

        //ambil data anggaran untuk ref status
        $datapagu = DB::table('data_ang')
                ->where('idrefstatus','=',$idrefstatus)
                ->where('header1','=',0)
                ->where('header2','=',0)
                ->select(DB::raw('kodeprogram, kodekegiatan, kodeoutput, kodesuboutput, kodekomponen, kodesubkomponen, kodeakun, pengenal, sum(total) as pagu, sum(nilaiblokir) as nilaiblokir'))
                ->groupBy(DB::raw('pengenal'))
                ->get();

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Program')
            ->setCellValue('C1', 'Kegiatan')
            ->setCellValue('D1', 'Output')
            ->setCellValue('E1', 'SubOutput')
            ->setCellValue('F1', 'Komponen')
            ->setCellValue('G1', 'Subkomponen')
            ->setCellValue('H1', 'Kode BKPK')
            ->setCellValue('I1', 'Kode Akun')
            ->setCellValue('J1', 'Pengenal')
            ->setCellValue('K1', 'No DIPA')
            ->setCellValue('L1', 'Tgl DIPA')
            ->setCellValue('M1', 'Pagu')
            ->setCellValue('N1', 'Nilai Blokir')
            ->setCellValue('O1', 'Revisi Ke')
            ->setCellValue('P1', 'Tgl Revisi')
            ->setCellValue('Q1', 'Anak Satker')
            ->setCellValue('R1', 'Kode Satker');



        $kolom = 2;
        $nomor = 1;


        foreach($datapagu as $data) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->kodeprogram)
                ->setCellValue('C' . $kolom, $data->kodekegiatan)
                ->setCellValue('D' . $kolom, $data->kodeoutput)
                ->setCellValue('E' . $kolom, $data->kodesuboutput)
                ->setCellValue('F' . $kolom, $data->kodekomponen)
                ->setCellValue('G' . $kolom, $data->kodesubkomponen)
                ->setCellValue('H' . $kolom, substr($data->kodeakun,0,4))
                ->setCellValue('I' . $kolom, $data->kodeakun)
                ->setCellValue('J' . $kolom, $data->pengenal)
                ->setCellValue('K' . $kolom, $nodipa)
                ->setCellValue('L' . $kolom, $tgl_dipa)
                ->setCellValue('M' . $kolom, $data->pagu)
                ->setCellValue('N' . $kolom, $data->nilaiblokir)
                ->setCellValue('O' . $kolom, $revisike)
                ->setCellValue('P' . $kolom, $tgl_revisi)
                ->setCellValue('Q' . $kolom, 0)
                ->setCellValue('R' . $kolom, $kdsatker);

            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data Pagu '.$kdsatker.' '.$kd_sts_history;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function summarydipa($idrefstatus){
        $tahunanggaran = session('tahunanggaran');
        $adadata = DB::table('sumarydipa')->where('idrefstatus','=',$idrefstatus)->count();
        if ($adadata > 0) {
            DB::table('sumarydipa')->where('idrefstatus','=',$idrefstatus)->delete();
            $datapagu = DB::table('data_ang')
                ->where('idrefstatus','=',$idrefstatus)
                ->where('header1','=',0)
                ->where('header2','=',0)
                ->select(DB::raw('pengenal, kdsatker, sum(total) as anggaran, sum(poknilai1) as pok1, sum(poknilai2) as pok2, sum(poknilai3) as pok3,
                                                             sum(poknilai4) as pok4, sum(poknilai5) as pok5, sum(poknilai6) as pok6,
                                                             sum(poknilai7) as pok7, sum(poknilai8) as pok8, sum(poknilai9) as pok9,
                                                             sum(poknilai10) as pok10, sum(poknilai11) as pok11, sum(poknilai12) as pok12, sum(nilaiblokir) as nilaiblokir'))
                ->groupBy(DB::raw('pengenal'))
                ->get();
            foreach ($datapagu as $item){
                $kdsatker = $item->kdsatker;
                $pengenal = $item->pengenal;
                $idbagian = DB::table('anggaranbagian')->where('pengenal',$pengenal)->value('idbagian');
                $jenisbelanja = substr($pengenal,22,2);
                $idbiro = Bagian::where('id',$idbagian)->value('idbiro');
                $iddeputi = Bagian::where('id',$idbagian)->value('iddeputi');
                $anggaran = $item->anggaran;
                $pok1 = $item->pok1;
                $pok2 = $item->pok2;
                $pok3 = $item->pok3;
                $pok4 = $item->pok4;
                $pok5 = $item->pok5;
                $pok6 = $item->pok6;
                $pok7 = $item->pok7;
                $pok8 = $item->pok8;
                $pok9 = $item->pok9;
                $pok10 = $item->pok10;
                $pok11 = $item->pok11;
                $pok12 = $item->pok12;
                $nilaiblokir = $item->nilaiblokir;

                $data = array(
                    'tahunanggaran' => $tahunanggaran,
                    'kdsatker' => $kdsatker,
                    'idrefstatus' => $idrefstatus,
                    'pengenal' => $pengenal,
                    'jenisbelanja' => $jenisbelanja,
                    'idbagian' => $idbagian,
                    'idbiro' => $idbiro,
                    'iddeputi' => $iddeputi,
                    'anggaran' => $anggaran,
                    'pok1' => $pok1,
                    'pok2' => $pok2,
                    'pok3' => $pok3,
                    'pok4' => $pok4,
                    'pok5' => $pok5,
                    'pok6' => $pok6,
                    'pok7' => $pok7,
                    'pok8' => $pok8,
                    'pok9' => $pok9,
                    'pok10' => $pok10,
                    'pok11' => $pok11,
                    'pok12' => $pok12,
                    'nilaiblokir' => $nilaiblokir,
                );
                DB::table('sumarydipa')->insert($data);
            }

        }else{
            $datapagu = DB::table('data_ang')
                ->where('idrefstatus','=',$idrefstatus)
                ->where('header1','=',0)
                ->where('header2','=',0)
                ->select(DB::raw('pengenal, kdsatker, sum(total) as anggaran, sum(poknilai1) as pok1, sum(poknilai2) as pok2, sum(poknilai3) as pok3,
                                                             sum(poknilai4) as pok4, sum(poknilai5) as pok5, sum(poknilai6) as pok6,
                                                             sum(poknilai7) as pok7, sum(poknilai8) as pok8, sum(poknilai9) as pok9,
                                                             sum(poknilai10) as pok10, sum(poknilai11) as pok11, sum(poknilai12) as pok12, sum(nilaiblokir) as nilaiblokir'))
                ->groupBy(DB::raw('pengenal'))
                ->get();

            foreach ($datapagu as $item){
                $kdsatker = $item->kdsatker;
                $pengenal = $item->pengenal;
                $anggaran = $item->anggaran;
                $jenisbelanja = substr($pengenal,22,2);
                $pok1 = $item->pok1;
                $pok2 = $item->pok2;
                $pok3 = $item->pok3;
                $pok4 = $item->pok4;
                $pok5 = $item->pok5;
                $pok6 = $item->pok6;
                $pok7 = $item->pok7;
                $pok8 = $item->pok8;
                $pok9 = $item->pok9;
                $pok10 = $item->pok10;
                $pok11 = $item->pok11;
                $pok12 = $item->pok12;
                $nilaiblokir = $item->nilaiblokir;

                $data = array(
                    'tahunanggaran' => $tahunanggaran,
                    'kdsatker' => $kdsatker,
                    'idrefstatus' => $idrefstatus,
                    'pengenal' => $pengenal,
                    'jenisbelanja' => $jenisbelanja,
                    'anggaran' => $anggaran,
                    'pok1' => $pok1,
                    'pok2' => $pok2,
                    'pok3' => $pok3,
                    'pok4' => $pok4,
                    'pok5' => $pok5,
                    'pok6' => $pok6,
                    'pok7' => $pok7,
                    'pok8' => $pok8,
                    'pok9' => $pok9,
                    'pok10' => $pok10,
                    'pok11' => $pok11,
                    'pok12' => $pok12,
                    'nilaiblokir' => $nilaiblokir,
                );
                DB::table('sumarydipa')->insert($data);
            }
        }
    }


}
