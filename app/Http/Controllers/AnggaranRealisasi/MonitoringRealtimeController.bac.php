<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\DataAng;
use App\Models\AnggaranRealisasi\Realisasi;
use App\Models\AnggaranRealisasi\RefStatus;
use App\Models\ReferensiUnit\Biro;
use App\Models\ReferensiUnit\Deputi;
use Illuminate\Http\Request;
use App\Models\ReferensiUnit\Bagian;
use App\Models\AnggaranRealisasi\AnggaranBagian;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\VarDumper\Cloner\Data;

class MonitoringRealtimeController extends Controller
{
    public function monitorrealisasi(){
        return view('AnggaranRealisasi.monitoringrealisasi');
    }

    public function aksirekapdata(Request $request){
        $tahunanggaran = session('tahunanggaran');
        $tanggallaporan = $request->tanggalcetak;
        $satker = $request->satker;
        $tanggallaporan = date('Y-m-d', strtotime($tanggallaporan));
        $isiantanggal = explode('-',$tanggallaporan);
        $tahun = $isiantanggal[0];
        if ($tahun > $tahunanggaran){
            return redirect('anggaran/monitoringrealisasi')->with('gagal', 'Tanggal Laporan Yang Dipilih Lebih Besar dari Tahun Anggaran');
        }else{
            $idrefstatus = RefStatus::where([
                ['tahunanggaran','=',$tahunanggaran],
                ['kd_sts_history','LIKE','B%'],
                ['tgl_revisi','<=',$tanggallaporan],
                ['kdsatker','=',$satker],
            ])->orwhere([
                ['tahunanggaran','=',$tahunanggaran],
                ['tgl_revisi','<=',$tanggallaporan],
                ['kd_sts_history','LIKE','C%'],
                ['flag_update_coa','=',1],
                ['kdsatker','=',$satker],
            ])->max('idrefstatus');


            /*
            $data = DB::table('bagian')
                ->leftJoin('anggaranbagian','bagian.id','=','anggaranbagian.idbagian')
                ->leftJoin('data_ang',function($join) use($idrefstatus){
                    $join->on('anggaranbagian.pengenal','=','data_ang.pengenal');
                })
                ->leftJoin('realisasi',function($join) use($tanggallaporan){
                    $join->on('realisasi.pengenal','=','anggaranbagian.pengenal');
                })
                ->leftJoin('deputi','bagian.iddeputi','=','deputi.id')
                ->leftJoin('biro','bagian.idbiro','=','biro.id')
                ->select('deputi.uraiandeputi as deputi','biro.uraianbiro as biro','bagian.uraianbagian as bagian','anggaranbagian.pengenal',DB::raw('SUM(data_ang.total) as anggaran'), DB::raw('sum(realisasi.nilairupiah) as realisasi'))
                ->where('data_ang.header1','=',0)
                ->where('data_ang.header2','=',0)
                ->where('data_ang.idrefstatus','=',$idrefstatus)
                ->where('tgl_sp2d','<=',$tanggallaporan)
                ->groupBy('bagian')
                ->get();
            */

            $databagian = Bagian::all();
            $datamonitoring = array();
            foreach ($databagian as $data){
                $iddeputi = $data->iddeputi;
                $uraiandeputi = Deputi::find($iddeputi)->get('uraiandeputi');
                $idbiro = $data->idbiro;
                $uraianbiro = Biro::find($idbiro)->get('uraianbiro');
                $idbagian = $data->id;
                $uraianbagian = $data->uraianbagian;

                //daptkan data kewenangan anggaran
                $dataanggaranbagian = AnggaranBagian::where('idbagian','=',$idbagian)->get();
                $anggaranbagian = 0;
                $realisasibagian = 0;
                foreach ($dataanggaranbagian as $a){
                    $pengenal = $a->pengenal;

                    //dapatkan data nilai anggarannya
                    $anggaran = DataAng::where([
                        ['idrefstatus','=',$idrefstatus],
                        ['header1','=',0],
                        ['header2','=',0],
                        ['pengenal','=',$pengenal]
                    ])->sum('total');
                    $anggaranbagian = $anggaranbagian + $anggaran;

                    //dapatkan nilai realisasi
                    $realisasi = Realisasi::where([
                        ['pengenal','=',$pengenal],
                        ['tgl_sp2d','<=',$tanggallaporan],
                    ])->sum('nilairupiah');
                    $realisasibagian = $realisasibagian + $realisasi;
                }
                $sisaanggaranbagian = $anggaranbagian - $realisasibagian;
                $datamonitoringbagian = array(
                    'deputi' => $uraiandeputi,
                    'biro' => $uraianbiro,
                    'bagian' => $uraianbagian,
                    'anggaran' => $anggaranbagian,
                    'realisasi' => $realisasibagian,
                    'sisa' => $anggaranbagian - $realisasibagian
                );
                $datamonitoring = array_merge($datamonitoring,$datamonitoringbagian);
            }


            $spreadsheet = new Spreadsheet();

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Nomor')
                ->setCellValue('B1', 'Deputi')
                ->setCellValue('C1', 'Biro')
                ->setCellValue('D1', 'Bagian')
                ->setCellValue('E1', 'Anggaran')
                ->setCellValue('F1', 'Realisasi')
                ->setCellValue('G1', 'Sisa Pagu');



            $kolom = 2;
            $nomor = 1;
            foreach($datamonitoring as $data) {
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $kolom, $nomor)
                    ->setCellValue('B' . $kolom, $data->deputi)
                    ->setCellValue('C' . $kolom, $data->biro)
                    ->setCellValue('D' . $kolom, $data->bagian)
                    ->setCellValue('E' . $kolom, $data->anggaran)
                    ->setCellValue('F' . $kolom, $data->realisasi)
                    ->setCellValue('G' . $kolom, $data->anggaran - $data->realisasi);

                $kolom++;
                $nomor++;
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Laporan Realisasi sd '.$tanggallaporan;

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
            header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            die();
        }
    }

}
