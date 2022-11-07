<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\RefStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportRealisasiController extends Controller
{
    public function monitorrealisasi(){
        return view('AnggaranRealisasi.monitoringrealisasi');
    }

    public function aksirekapdata(Request $request){
        $satker = $request->satker;
        $bulan = $request->bulan;
        $periodesasi = $request->periodesasi;

       if ($satker != 'lembaga'){
           if ($periodesasi == 1){
               $this->realisasibulansatker($satker, $bulan);
           }else{
               $this->realisasisdbulansatker($satker, $bulan);
           }
       }else{
           if ($periodesasi == 1){
                $this->realisasibulanlembaga($bulan);
           }else{
                $this->realisasisdbulanlembaga($bulan);
           }
       }

    }

    public function realisasibulansatker($satker, $bulan){
        $tahunanggaran = session('tahunanggaran');

        //ambil idrefterakhir
        $dipaAwal = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satker.' and kd_sts_history = "B00"')->max('idrefstatus');
        $maxRevisiDipa = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOK = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatus = ($maxRevisiPOK > $maxRevisiDipa ? $maxRevisiPOK : $maxRevisiDipa);

        $datarealisasi = DB::table('anggaranbagian as a')
            ->select(['a.pengenal as pengenal', 'b.uraianbagian as bagian', 'c.uraianbiro as biro','d.uraiandeputi as deputi', DB::raw('f.anggaran as diparevisi'),
                'realisasiproses' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(h.nilairupiah)')])
                        ->from('realisasi as h')
                        ->whereColumn('a.pengenal','h.pengenal')
                        ->where('h.tahunanggaran','=',$tahunanggaran)
                        ->whereRaw('month(h.tgl_sp2d) = '.$bulan)
                        ->whereNull('h.no_sp2d');
                },'realisasi' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(g.nilairupiah)')])
                        ->from('realisasi as g')
                        ->whereColumn('a.pengenal','g.pengenal')
                        ->where('g.tahunanggaran','=',$tahunanggaran)
                        ->whereRaw('month(g.tgl_sp2d) = '.$bulan);
                },'realisasisemar' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilai_tagihan)')])
                        ->from('realisasisemar as i')
                        ->whereColumn('a.pengenal','i.pengenal')
                        ->whereRaw('month(i.tanggal_kwitansi_karwas) = '.$bulan)
                        ->whereRaw('year(i.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                },'dipaawal' => function($query) use ($tahunanggaran, $dipaAwal){
                    $query->select('e.anggaran')
                        ->from('sumarydipa as e')
                        ->whereColumn('a.pengenal','e.pengenal')
                        ->where('e.idrefstatus','=',$dipaAwal)
                        ->where('e.tahunanggaran','=',$tahunanggaran);
                }])
            ->leftJoin('sumarydipa as f', function($join) use($tahunanggaran, $idrefstatus){
                $join->on('a.pengenal','=','f.pengenal')
                    ->where('f.idrefstatus','=',$idrefstatus)
                    ->where('f.tahunanggaran','=',$tahunanggaran);
            })
            ->where('a.tahunanggaran','=',$tahunanggaran)
            ->where('a.kdsatker','=',$satker)
            ->leftJoin('bagian as b','a.idbagian','=','b.id')
            ->leftJoin('biro as c','b.idbiro','=','c.id')
            ->leftJoin('deputi as d','b.iddeputi','=','d.id')
            ->groupBy('a.pengenal')
            ->get();

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Deputi')
            ->setCellValue('C1', 'Biro')
            ->setCellValue('D1', 'Bagian')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Dipa Awal')
            ->setCellValue('G1', 'Dipa Revisi')
            ->setCellValue('H1', 'Realisasi SP2D')
            ->setCellValue('I1', 'Realisasi Proses')
            ->setCellValue('J1', 'Total Realisasi')
            ->setCellValue('K1', 'Realisasi Semar')
            ->setCellValue('L1', 'Sisa Pagu')
            ->setCellValue('M1', '% Realisasi SP2D')
            ->setCellValue('N1', '% Realisasi SEMAR');

        $kolom = 2;
        $nomor = 1;

        foreach($datarealisasi as $data) {
            if ($data->diparevisi == 0){
                $prosentaserealisasisp2d = 0;
                $prosentaserealisasisemar = 0;
            }else{
                $prosentaserealisasisp2d = number_format((($data->realisasi/$data->diparevisi)*100),2,',','.');
                $prosentaserealisasisemar = number_format((($data->realisasisemar/$data->diparevisi)*100),2,',','.');
            }
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->deputi)
                ->setCellValue('C' . $kolom, $data->biro)
                ->setCellValue('D' . $kolom, $data->bagian)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->dipaawal)
                ->setCellValue('G' . $kolom, $data->diparevisi)
                ->setCellValue('H' . $kolom, $data->realisasi - $data->realisasiproses)
                ->setCellValue('I' . $kolom, $data->realisasiproses)
                ->setCellValue('J' . $kolom, $data->realisasi)
                ->setCellValue('K' . $kolom, $data->realisasisemar)
                ->setCellValue('L' . $kolom, $data->diparevisi - $data->realisasi)
                ->setCellValue('M' . $kolom, $prosentaserealisasisp2d)
                ->setCellValue('N' . $kolom, $prosentaserealisasisemar);

            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan Realisasi '.$satker.'sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function realisasisdbulansatker($satker, $bulan){
        $tahunanggaran = session('tahunanggaran');

        //ambil idrefterakhir
        $dipaAwal = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satker.' and kd_sts_history = "B00"')->max('idrefstatus');
        $maxRevisiDipa = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOK = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satker.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatus = ($maxRevisiPOK > $maxRevisiDipa ? $maxRevisiPOK : $maxRevisiDipa);

        $datarealisasi = DB::table('anggaranbagian as a')
            ->select(['a.pengenal as pengenal', 'b.uraianbagian as bagian', 'c.uraianbiro as biro','d.uraiandeputi as deputi', DB::raw('f.anggaran as diparevisi'),
                'realisasiproses' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(h.nilairupiah)')])
                        ->from('realisasi as h')
                        ->whereColumn('a.pengenal','h.pengenal')
                        ->where('h.tahunanggaran','=',$tahunanggaran)
                        ->whereRaw('month(h.tgl_sp2d) <= '.$bulan)
                        ->whereNull('h.no_sp2d');
                },'realisasi' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(g.nilairupiah)')])
                        ->from('realisasi as g')
                        ->whereColumn('a.pengenal','g.pengenal')
                        ->where('g.tahunanggaran','=',$tahunanggaran)
                        ->whereRaw('month(g.tgl_sp2d) <= '.$bulan);
                },'realisasisemar' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilai_tagihan)')])
                        ->from('realisasisemar as i')
                        ->whereColumn('a.pengenal','i.pengenal')
                        ->whereRaw('month(i.tanggal_kwitansi_karwas) <= '.$bulan)
                        ->whereRaw('year(i.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                },'dipaawal' => function($query) use ($tahunanggaran, $dipaAwal){
                    $query->select('e.anggaran')
                        ->from('sumarydipa as e')
                        ->whereColumn('a.pengenal','e.pengenal')
                        ->where('e.idrefstatus','=',$dipaAwal)
                        ->where('e.tahunanggaran','=',$tahunanggaran);
                }])
            ->leftJoin('sumarydipa as f', function($join) use($tahunanggaran, $idrefstatus){
                $join->on('a.pengenal','=','f.pengenal')
                    ->where('f.idrefstatus','=',$idrefstatus)
                    ->where('f.tahunanggaran','=',$tahunanggaran);
            })
            ->where('a.tahunanggaran','=',$tahunanggaran)
            ->where('a.kdsatker','=',$satker)
            ->leftJoin('bagian as b','a.idbagian','=','b.id')
            ->leftJoin('biro as c','b.idbiro','=','c.id')
            ->leftJoin('deputi as d','b.iddeputi','=','d.id')
            ->groupBy('a.pengenal')
            ->get();

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Deputi')
            ->setCellValue('C1', 'Biro')
            ->setCellValue('D1', 'Bagian')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Dipa Awal')
            ->setCellValue('G1', 'Dipa Revisi')
            ->setCellValue('H1', 'Realisasi SP2D')
            ->setCellValue('I1', 'Realisasi Proses')
            ->setCellValue('J1', 'Total Realisasi')
            ->setCellValue('K1', 'Realisasi Semar')
            ->setCellValue('L1', 'Sisa Pagu')
            ->setCellValue('M1', '% Realisasi SP2D')
            ->setCellValue('N1', '% Realisasi SEMAR');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasi as $data) {
            if ($data->diparevisi == 0){
                $prosentaserealisasisp2d = 0;
                $prosentaserealisasisemar = 0;
            }else{
                $prosentaserealisasisp2d = number_format((($data->realisasi/$data->diparevisi)*100),2,',','.');
                $prosentaserealisasisemar = number_format((($data->realisasisemar/$data->diparevisi)*100),2,',','.');
            }
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->deputi)
                ->setCellValue('C' . $kolom, $data->biro)
                ->setCellValue('D' . $kolom, $data->bagian)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->dipaawal)
                ->setCellValue('G' . $kolom, $data->diparevisi)
                ->setCellValue('H' . $kolom, $data->realisasi - $data->realisasiproses)
                ->setCellValue('I' . $kolom, $data->realisasiproses)
                ->setCellValue('J' . $kolom, $data->realisasi)
                ->setCellValue('K' . $kolom, $data->realisasisemar)
                ->setCellValue('L' . $kolom, $data->diparevisi - $data->realisasi)
                ->setCellValue('M' . $kolom, $prosentaserealisasisp2d)
                ->setCellValue('N' . $kolom, $prosentaserealisasisemar);

            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan Realisasi '.$satker.' sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function realisasisdbulanlembaga($bulan){
        $tahunanggaran = session('tahunanggaran');
        $satkerdewan = '001030';
        $satkersetjen = '001012';

        //ambil data revisi setjen
        $dipaAwalsetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and kd_sts_history = "B00"')->max('idrefstatus');
        $maxRevisiDipaSetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOKSetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatusSetjen = ($maxRevisiPOKSetjen > $maxRevisiDipaSetjen ? $maxRevisiPOKSetjen : $maxRevisiDipaSetjen);

        //ambil data revisi dewan
        $dipaAwalDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and kd_sts_history = "B00"')->max('idrefstatus');
        $maxRevisiDipaDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOKDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatusDewan = ($maxRevisiPOKDewan > $maxRevisiDipaDewan ? $maxRevisiPOKDewan : $maxRevisiDipaDewan);

        //ambil data realisasi
        $datarealisasisetjen = DB::table('anggaranbagian as a')
            ->select(['a.pengenal as pengenal','a.kdsatker as satker', 'b.uraianbagian as bagian', 'c.uraianbiro as biro','d.uraiandeputi as deputi',
                'e.anggaran as dipaawal', 'f.anggaran as diparevisi',
                'realisasiproses' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as h')
                        ->whereColumn('a.pengenal','h.pengenal')
                        ->where('h.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(h.tgl_sp2d) <= '.$bulan)
                        ->whereNull('h.no_sp2d');
                },'realisasi' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as g')
                        ->whereColumn('a.pengenal','g.pengenal')
                        ->where('g.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(g.tgl_sp2d) <= '.$bulan);
                },'realisasisemar' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilai_tagihan)')])
                        ->from('realisasisemar as i')
                        ->whereColumn('a.pengenal','i.pengenal')
                        ->whereRaw('month(i.tanggal_kwitansi_karwas) <= '.$bulan)
                        ->whereRaw('year(i.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                }])
            ->where('a.tahunanggaran','=',$tahunanggaran)
            ->where('a.kdsatker','=',$satkersetjen)
            ->leftJoin('bagian as b','a.idbagian','=','b.id')
            ->leftJoin('biro as c','b.idbiro','=','c.id')
            ->leftJoin('deputi as d','b.iddeputi','=','d.id')
            ->leftJoin('sumarydipa as e', function($join) use($tahunanggaran, $dipaAwalsetjen){
                $join->on('a.pengenal','=','e.pengenal')
                    ->where('e.idrefstatus','=',$dipaAwalsetjen)
                    ->where('e.tahunanggaran','=',$tahunanggaran);
            })
            ->leftJoin('sumarydipa as f', function($join) use($tahunanggaran, $idrefstatusSetjen){
                $join->on('a.pengenal','=','f.pengenal')
                    ->where('f.idrefstatus','=',$idrefstatusSetjen)
                    ->where('f.tahunanggaran','=',$tahunanggaran);
            })
            ->groupBy('a.pengenal');

        $datarealisasilembaga = DB::table('anggaranbagian as a')
            ->select(['a.pengenal as pengenal','a.kdsatker as satker' ,'b.uraianbagian as bagian', 'c.uraianbiro as biro','d.uraiandeputi as deputi',
                'e.anggaran as dipaawal', 'f.anggaran as diparevisi',
                'realisasiproses' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as h')
                        ->whereColumn('a.pengenal','h.pengenal')
                        ->where('h.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(h.tgl_sp2d) <= '.$bulan)
                        ->whereNull('h.no_sp2d');
                },
                'realisasi' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as g')
                        ->whereColumn('a.pengenal','g.pengenal')
                        ->where('g.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(g.tgl_sp2d) <= '.$bulan);
                },'realisasisemar' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilai_tagihan)')])
                        ->from('realisasisemar as i')
                        ->whereColumn('a.pengenal','i.pengenal')
                        ->whereRaw('month(i.tanggal_kwitansi_karwas) <= '.$bulan)
                        ->whereRaw('year(i.tanggal_kwitansi_karwas) <= '.$tahunanggaran);
                }])
            ->where('a.tahunanggaran','=',$tahunanggaran)
            ->where('a.kdsatker','=',$satkerdewan)
            ->leftJoin('bagian as b','a.idbagian','=','b.id')
            ->leftJoin('biro as c','b.idbiro','=','c.id')
            ->leftJoin('deputi as d','b.iddeputi','=','d.id')
            ->leftJoin('sumarydipa as e', function($join) use($tahunanggaran, $dipaAwalDewan){
                $join->on('a.pengenal','=','e.pengenal')
                    ->where('e.idrefstatus','=',$dipaAwalDewan)
                    ->where('e.tahunanggaran','=',$tahunanggaran);
            })
            ->leftJoin('sumarydipa as f', function($join) use($tahunanggaran, $idrefstatusDewan){
                $join->on('a.pengenal','=','f.pengenal')
                    ->where('f.idrefstatus','=',$idrefstatusDewan)
                    ->where('f.tahunanggaran','=',$tahunanggaran);
            })
            ->groupBy('a.pengenal')
            ->union($datarealisasisetjen)
            ->get();


        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Deputi')
            ->setCellValue('C1', 'Biro')
            ->setCellValue('D1', 'Bagian')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Dipa Awal')
            ->setCellValue('G1', 'Dipa Revisi')
            ->setCellValue('H1', 'Realisasi SP2D')
            ->setCellValue('I1', 'Realisasi Proses')
            ->setCellValue('J1', 'Total Realisasi')
            ->setCellValue('K1', 'Realisasi Semar')
            ->setCellValue('L1', 'Sisa Pagu')
            ->setCellValue('M1', '% Realisasi SP2D')
            ->setCellValue('N1', '% Realisasi SEMAR');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasilembaga as $data) {
            if ($data->diparevisi == 0){
                $prosentaserealisasisp2d = 0;
                $prosentaserealisasisemar = 0;
            }else{
                $prosentaserealisasisp2d = number_format((($data->realisasi/$data->diparevisi)*100),2,',','.');
                $prosentaserealisasisemar = number_format((($data->realisasisemar/$data->diparevisi)*100),2,',','.');
            }
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->deputi)
                ->setCellValue('C' . $kolom, $data->biro)
                ->setCellValue('D' . $kolom, $data->bagian)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->dipaawal)
                ->setCellValue('G' . $kolom, $data->diparevisi)
                ->setCellValue('H' . $kolom, $data->realisasi - $data->realisasiproses)
                ->setCellValue('I' . $kolom, $data->realisasiproses)
                ->setCellValue('J' . $kolom, $data->realisasi)
                ->setCellValue('K' . $kolom, $data->realisasisemar)
                ->setCellValue('L' . $kolom, $data->diparevisi - $data->realisasi)
                ->setCellValue('M' . $kolom, $prosentaserealisasisp2d)
                ->setCellValue('N' . $kolom, $prosentaserealisasisemar);

            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan Realisasi DPR RI'.' Sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function realisasibulanlembaga($bulan){
        $tahunanggaran = session('tahunanggaran');
        $satkerdewan = '001030';
        $satkersetjen = '001012';

        //ambil data revisi setjen
        $dipaAwalsetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and kd_sts_history = "B00"')->max('idrefstatus');
        $maxRevisiDipaSetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOKSetjen = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkersetjen.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatusSetjen = ($maxRevisiPOKSetjen > $maxRevisiDipaSetjen ? $maxRevisiPOKSetjen : $maxRevisiDipaSetjen);

        //ambil data revisi dewan
        $dipaAwalDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and kd_sts_history = "B00"')->max('idrefstatus');
        $maxRevisiDipaDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "B%"')->max('idrefstatus');
        $maxRevisiPOKDewan = RefStatus::whereRaw('tahunanggaran = '.$tahunanggaran.' and kdsatker = '.$satkerdewan.' and month(tgl_revisi) <= '.$bulan.' and kd_sts_history LIKE "C%" and flag_update_coa = 1')->max('idrefstatus');
        $idrefstatusDewan = ($maxRevisiPOKDewan > $maxRevisiDipaDewan ? $maxRevisiPOKDewan : $maxRevisiDipaDewan);


        //ambil data realisasi
        $datarealisasisetjen = DB::table('anggaranbagian as a')
            ->select(['a.pengenal as pengenal','a.kdsatker as satker', 'b.uraianbagian as bagian', 'c.uraianbiro as biro','d.uraiandeputi as deputi',
                'e.anggaran as dipaawal', 'f.anggaran as diparevisi',
                'realisasiproses' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as h')
                        ->whereColumn('a.pengenal','h.pengenal')
                        ->where('h.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(h.tgl_sp2d) = '.$bulan)
                        ->whereNull('h.no_sp2d');
                },
                'realisasi' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as g')
                        ->whereColumn('a.pengenal','g.pengenal')
                        ->where('g.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(g.tgl_sp2d) = '.$bulan);
                },'realisasisemar' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilai_tagihan)')])
                        ->from('realisasisemar as i')
                        ->whereColumn('a.pengenal','i.pengenal')
                        ->whereRaw('month(i.tanggal_kwitansi_karwas) = '.$bulan)
                        ->whereRaw('year(i.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                }])
            ->where('a.tahunanggaran','=',$tahunanggaran)
            ->where('a.kdsatker','=',$satkersetjen)
            ->leftJoin('bagian as b','a.idbagian','=','b.id')
            ->leftJoin('biro as c','b.idbiro','=','c.id')
            ->leftJoin('deputi as d','b.iddeputi','=','d.id')
            ->leftJoin('sumarydipa as e', function($join) use($tahunanggaran, $dipaAwalsetjen){
                $join->on('a.pengenal','=','e.pengenal')
                    ->where('e.idrefstatus','=',$dipaAwalsetjen)
                    ->where('e.tahunanggaran','=',$tahunanggaran);
            })
            ->leftJoin('sumarydipa as f', function($join) use($tahunanggaran, $idrefstatusSetjen){
                $join->on('a.pengenal','=','f.pengenal')
                    ->where('f.idrefstatus','=',$idrefstatusSetjen)
                    ->where('f.tahunanggaran','=',$tahunanggaran);
            })
            ->groupBy('a.pengenal');

        $datarealisasilembaga = DB::table('anggaranbagian as a')
            ->select(['a.pengenal as pengenal','a.kdsatker as satker', 'b.uraianbagian as bagian', 'c.uraianbiro as biro','d.uraiandeputi as deputi',
                'e.anggaran as dipaawal', 'f.anggaran as diparevisi',
                'realisasiproses' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as h')
                        ->whereColumn('a.pengenal','h.pengenal')
                        ->where('h.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(h.tgl_sp2d) = '.$bulan)
                        ->whereNull('h.no_sp2d');
                },'realisasi' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilairupiah)')])
                        ->from('realisasi as g')
                        ->whereColumn('a.pengenal','g.pengenal')
                        ->where('g.tahunanggaran',$tahunanggaran)
                        ->whereRaw('month(g.tgl_sp2d) = '.$bulan);
                },'realisasisemar' => function($query) use ($tahunanggaran, $bulan){
                    $query->select([DB::raw('sum(nilai_tagihan)')])
                        ->from('realisasisemar as i')
                        ->whereColumn('a.pengenal','i.pengenal')
                        ->whereRaw('month(i.tanggal_kwitansi_karwas) = '.$bulan)
                        ->whereRaw('year(i.tanggal_kwitansi_karwas) = '.$tahunanggaran);
                }])
            ->where('a.tahunanggaran','=',$tahunanggaran)
            ->where('a.kdsatker','=',$satkerdewan)
            ->leftJoin('bagian as b','a.idbagian','=','b.id')
            ->leftJoin('biro as c','b.idbiro','=','c.id')
            ->leftJoin('deputi as d','b.iddeputi','=','d.id')
            ->leftJoin('sumarydipa as e', function($join) use($tahunanggaran, $dipaAwalDewan){
                $join->on('a.pengenal','=','e.pengenal')
                    ->where('e.idrefstatus','=',$dipaAwalDewan)
                    ->where('e.tahunanggaran','=',$tahunanggaran);
            })
            ->leftJoin('sumarydipa as f', function($join) use($tahunanggaran, $idrefstatusDewan){
                $join->on('a.pengenal','=','f.pengenal')
                    ->where('f.idrefstatus','=',$idrefstatusDewan)
                    ->where('f.tahunanggaran','=',$tahunanggaran);
            })
            ->groupBy('a.pengenal')
            ->union($datarealisasisetjen)
            ->get();


        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Deputi')
            ->setCellValue('C1', 'Biro')
            ->setCellValue('D1', 'Bagian')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Dipa Awal')
            ->setCellValue('G1', 'Dipa Revisi')
            ->setCellValue('H1', 'Realisasi SP2D')
            ->setCellValue('I1', 'Realisasi Proses')
            ->setCellValue('J1', 'Total Realisasi')
            ->setCellValue('K1', 'Realisasi Semar')
            ->setCellValue('L1', 'Sisa Pagu')
            ->setCellValue('M1', '% Realisasi SP2D')
            ->setCellValue('N1', '% Realisasi SEMAR');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasilembaga as $data) {
            if ($data->diparevisi == 0){
                $prosentaserealisasisp2d = 0;
                $prosentaserealisasisemar = 0;
            }else{
                $prosentaserealisasisp2d = number_format((($data->realisasi/$data->diparevisi)*100),2,',','.');
                $prosentaserealisasisemar = number_format((($data->realisasisemar/$data->diparevisi)*100),2,',','.');
            }
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->deputi)
                ->setCellValue('C' . $kolom, $data->biro)
                ->setCellValue('D' . $kolom, $data->bagian)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->dipaawal)
                ->setCellValue('G' . $kolom, $data->diparevisi)
                ->setCellValue('H' . $kolom, $data->realisasi - $data->realisasiproses)
                ->setCellValue('I' . $kolom, $data->realisasiproses)
                ->setCellValue('J' . $kolom, $data->realisasi)
                ->setCellValue('K' . $kolom, $data->realisasisemar)
                ->setCellValue('L' . $kolom, $data->diparevisi - $data->realisasi)
                ->setCellValue('M' . $kolom, $prosentaserealisasisp2d)
                ->setCellValue('N' . $kolom, $prosentaserealisasisemar);

            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan Realisasi DPR RI'.' Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();

    }


}
