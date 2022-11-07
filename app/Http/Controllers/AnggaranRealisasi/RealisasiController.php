<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use App\Http\Controllers\AdministrasiAplikasi\BearerKeyController;
use App\Models\AnggaranRealisasi\AnggaranBagian;
use App\Models\AnggaranRealisasi\Realisasi;
use App\Models\AnggaranRealisasi\TimeStampData;
use App\Models\ReferensiUnit\Bagian;
use DateTime;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Controller;
use App\Libraries\PeriodeLaporan;
use Illuminate\Support\Facades\DB;

class RealisasiController extends Controller
{
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

    private function _showOutput($judul, $lokasi, $output, $info) {
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
            'info' => $info
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

    public function tampilrealisasi()
    {
        $tahunanggaran = session('tahunanggaran');
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

        $lokasi = 'AnggaranRealisasi.realisasi';
        $judul = 'Daftar SP2D';
        $crud = $this->_getGroceryCrudEnterprise();

        $where = array(
            'tahunanggaran' => $tahunanggaran
        );
        $crud->where($where);
        $crud->setTable('realisasi');
        $crud->setSubject('Realisasi', 'Realisasi');
        $crud->columns(['kdsatker','no_spp','no_sp2d','tgl_sp2d','pengenal','kodeprogram','kodekegiatan','kodeoutput','kodesuboutput','kodekomponen','kodesubkomponen','kodeakun','uraian','nilairupiah']);
        $crud->displayAs('kdsatker','Satker');
        $crud->displayAs('no_spp','No SPP');
        $crud->displayAs('no_sp2d','No SP2D');
        $crud->displayAs('tgl_sp2d','Tgl SP2D');
        $crud->displayAs('kodeprogram','Program');
        $crud->displayAs('kodekegiatan','Kegiatan');
        $crud->displayAs('kodeoutput','Output');
        $crud->displayAs('kodesuboutput','Suboutput');
        $crud->displayAs('kodekomponen','Komponen');
        $crud->displayAs('kodesubkomponen','SubKomponen');
        $crud->displayAs('kodeakun','Akun');
        $crud->displayAs('pengenal','Pengenal');
        $crud->displayAs('uraian','Pekerjaan');
        $crud->displayAs('nilairupiah','Nilai');

        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output, $info);
    }

    public function importrealisasi(){
        $bearerkey = new BearerKeyController();
        $bearerkey = $bearerkey->dapatkanbearerkey();
        $key = $bearerkey;

        $tahunanggaran = session('tahunanggaran');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://monsakti.kemenkeu.go.id/sitp-monsakti-omspan/webservice/API/KL002/realisasi/',
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

        //hapus tabel realisasinya
        DB::table('realisasi')->truncate();

        //proses data hasil tarikan
        $diolah = substr(json_encode($response),10,14);
        if ($diolah !== "" && $diolah !== "<b>Fatal error" ) {
            $hasilasli = json_decode($response);
            foreach ($hasilasli as $item){
                $kdsatker = $item->KDSATKER;
                $kode_kementerian = $item->KODE_KEMENTERIAN;
                $tgl_sp2d = new \DateTime($item->TGL_SP2D);
                $tgl_sp2d = $tgl_sp2d->format('Y-m-d');
                $no_spp = $item->NO_SPP;
                $no_sp2d = $item->NO_SP2D;
                $uraian = $item->URAIAN;
                $kode_coa = $item->KODE_COA;
                $kodeprogram = substr($kode_coa,23,2);
                $kodekeegiatan = substr($kode_coa,26,4);
                $kodeoutput = substr($kode_coa,30,3);
                $kodesuboutput = substr($kode_coa,74,3);
                $kodekomponen = substr($kode_coa,78,3);
                $kodesubbkomponen = substr($kode_coa,83,1);
                $kodeakun = substr($kode_coa,11,6);
                $pengenal = $kodeprogram.'.'.$kodekeegiatan.'.'.$kodeoutput.'.'.$kodesuboutput.'.'.$kodekomponen.'.'.$kodesubbkomponen.'.'.$kodeakun;
                $idbagian = AnggaranBagian::where('pengenal','=',$pengenal)->value('idbagian');
                $idbiro = Bagian::where('id','=',$idbagian)->value('idbiro');
                $iddeputi = Bagian::where('id','=',$idbagian)->value('iddeputi');
                $mata_uang = $item->MATA_UANG;
                $KURS = $item->KURS;
                $nilai_valas = $item->NILAI_VALAS;
                $nilai_rupiah = $item->NILAI_RUPIAH;

                //001030.182.524111.00202CF.5804ABC.A000000001.00000.1.0151.2.000000.000000.001.052.0C.000000

                $data = array(
                    'tahunanggaran' => $tahunanggaran,
                    'kdsatker' => $kdsatker,
                    'kode_kementerian' => $kode_kementerian,
                    'tgl_sp2d' => $tgl_sp2d,
                    'no_spp' => $no_spp,
                    'no_sp2d' => $no_sp2d,
                    'uraian' => $uraian,
                    'kode_coa' => $kode_coa,
                    'kodeprogram' => $kodeprogram,
                    'kodekegiatan' => $kodekeegiatan,
                    'kodeoutput' => $kodeoutput,
                    'kodesuboutput' => $kodesuboutput,
                    'kodekomponen' => $kodekomponen,
                    'kodesubkomponen' => $kodesubbkomponen,
                    'kodeakun' => $kodeakun,
                    'pengenal' => $pengenal,
                    'idbagian' => $idbagian,
                    'idbiro' => $idbiro,
                    'iddeputi' => $iddeputi,
                    'mata_uang' => $mata_uang,
                    'kurs' => $KURS,
                    'nilaivalas' => $nilai_valas,
                    'nilairupiah' => $nilai_rupiah
                );
                Realisasi::insert($data);
            }

            //update waktu tarik terakhir
            $waktutarik = new DateTime();
            $waktuterakhirupdate = DB::table('timestampdata')->find(1);
            if ($waktuterakhirupdate){
                DB::table('timestampdata')->update(['updaterealisasi' => $waktutarik]);
            }else{
                DB::table('timestampdata')->insert(['updaterealisasi' => $waktutarik]);
            }
            //import realisasi semar
            $this->importrealisasisemar();
            return redirect()->to('anggaran/realisasi')->with('status','Import Realisasi Berhasil');
        }else{
            return redirect()->to('anggaran/realisasi')->with('status','Import Realisasi Gagal');
        }
    }

    public function importrealisasisemar(){
        $tahunanggaran = session('tahunanggaran');
        //ambil bulan sekarang
        $tanggalserver = new DateTime();
        $bulan = $tanggalserver->format('n');

        //rubah formatnya jd format semar
        $periodelaporan = New PeriodeLaporan();
        $periodelaporan = $periodelaporan->tanggallaporan($tahunanggaran, 1,$bulan);
        $token = array(
            'token' => 'samwitwicky'
        );

        $curlvariabel = array_merge($token, $periodelaporan);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://semar.dpr.go.id/api/karwas-pengenal',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($curlvariabel),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: PHPSESSID=kcvpdgh1e8p95he18a2n9i81p6'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        DB::table('realisasisemar')->truncate();

        $hasil = json_decode($response);
        foreach ($hasil as $item){
            $tanggal_spp_spby = $item->tanggal_spp_spby;
            $no_kwitansi_karwas = $item->no_kwitansi_karwas;
            $no_spp = $item->no_spp;
            $no_spby = $item->no_spby;
            $id_anak_satker = $item->id_anak_satker;
            $pengenal = $item->pengenal;
            $idbagian = AnggaranBagian::where('pengenal','=',$pengenal)->value('idbagian');
            $kdsatker = AnggaranBagian::where('pengenal','=',$pengenal)->value('kdsatker');
            $idbiro = Bagian::where('id','=',$idbagian)->value('idbiro');
            $iddeputi = Bagian::where('id','=',$idbagian)->value('iddeputi');
            $nama_rekanan = $item->nama_rekanan;
            $uraian_pekerjaan = $item->uraian_pekerjaan;
            $nilai_tagihan = $item->nilai_tagihan;
            $cara_bayar = $item->cara_bayar;
            $tanggal_pembayaran_kasbon = $item->tanggal_pembayaran_kasbon;
            $nama_penerima = $item->nama_penerima;
            $no_kwitansi_silabi = $item->no_kwitansi_silabi;
            $no_pembukuan_kwitansi_silabi = $item->no_pembukuan_kwitansi_silabi;
            $no_pajak_silabi = $item->no_pajak_silabi;
            $no_pembukuan_pajak_silabi = $item->no_pembukuan_pajak_silabi;
            $no_spm = $item->no_spm;
            $tanggal_spm = $item->tanggal_spm;
            $tanggal_sp2d = $item->tanggal_sp2d;
            $no_sp2d = $item->no_sp2d;
            $tanggal_kwitansi_karwas = $item->tanggal_kwitansi_karwas;
            $tahapan = $item->tahapan;

            $datainsert = array(
                'tanggal_spp_spby' => $tanggal_spp_spby,
                'no_kwitansi_karwas' => $no_kwitansi_karwas,
                'no_spp' => $no_spp,
                'no_spby' => $no_spby,
                'id_anak_satker' => $id_anak_satker,
                'pengenal' => $pengenal,
                'kdsatker' => $kdsatker,
                'idbagian' => $idbagian,
                'idbiro' => $idbiro,
                'iddeputi' => $iddeputi,
                'nama_rekanan' => $nama_rekanan,
                'uraian_pekerjaan' => $uraian_pekerjaan,
                'nilai_tagihan' => $nilai_tagihan,
                'cara_bayar' => $cara_bayar,
                'tanggal_pembayaran_kasbon' => $tanggal_pembayaran_kasbon,
                'nama_penerima' => $nama_penerima,
                'no_kwitansi_silabi' => $no_kwitansi_silabi,
                'no_pembukuan_kwitansi_silabi' => $no_pembukuan_kwitansi_silabi,
                'no_pajak_silabi' => $no_pajak_silabi,
                'no_pembukuan_pajak_silabi' => $no_pembukuan_pajak_silabi,
                'no_spm' => $no_spm,
                'tanggal_spm' => $tanggal_spm,
                'tanggal_sp2d' => $tanggal_sp2d,
                'no_sp2d' => $no_sp2d,
                'tanggal_kwitansi_karwas' => $tanggal_kwitansi_karwas,
                'tahapan' => $tahapan

            );
            DB::table('realisasisemar')->insert($datainsert);
        }
    }

}
