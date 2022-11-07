<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdministrasiAplikasi\BearerKeyController;
use App\Http\Controllers\AnggaranRealisasi\RealisasiController;
use App\Models\AnggaranRealisasi\AnggaranBagian;
use App\Models\AnggaranRealisasi\Realisasi;
use App\Models\ReferensiUnit\Bagian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use DateTime;

class UpdateRealisasiHourly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updaterealisasi:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Data Realisasi SAKTI dan SEMAR Setiap Jam';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
            $realisasisemar = new RealisasiController();
            $realisasisemar->importrealisasisemar();
        }
        return Command::SUCCESS;
    }
}
