<?php

namespace App\Http\Controllers\Ikpa;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\Realisasi;
use App\Models\ReferensiUnit\Bagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\DataTables;
use App\Models\Ikpa\RealisasiSemar;
use DateTime;

class DataRealisasiController extends Controller
{
    public function tampildataperbiro($idbiro, $satker){
        return view('ikpa.datarealisasisaktibiro',[
            'judul' => 'Data Realisasi Menurut SAKTI',
            'idbiro' => $idbiro,
            'satker' => $satker
        ]);
    }

    public function datarealisasisaktiperbiro(Request $request, $idbiro, $satker){
        if ($request->ajax()){
            //ambil bulan
            $tanggalserver = new DateTime();
            $bulan = $tanggalserver->format('n');
            $tahunanggaran = session('tahunanggaran');

            $where = array(
                'idbiro' => $idbiro,
                'kdsatker' => $satker,
                'tahunanggaran' => $tahunanggaran
            );
            $datarealisasisaktibiro = Realisasi::where($where)
                ->whereRaw('month(tgl_sp2d) <='.$bulan)
                ->get(['no_spp','no_sp2d','tgl_sp2d','pengenal','uraian','nilairupiah']);
            return Datatables::of($datarealisasisaktibiro)
                ->addIndexColumn()

                ->addColumn('nilairupiah',function($row){
                    $nilairupiah = $row->nilairupiah;
                    $nilairupiah = number_format($nilairupiah,0,',','.');
                    return $nilairupiah;

                })
                ->make(true);
        }

    }

    public function exportdatasaktibiro($satker, $biro){
        //ambil bulan
        $tanggalserver = new DateTime();
        $bulan = $tanggalserver->format('n');
        $tahunanggaran = session('tahunanggaran');

        $where = array(
            'idbiro' => $biro,
            'kdsatker' => $satker,
            'tahunanggaran' => $tahunanggaran
        );
        $datarealisasisaktibiro = Realisasi::where($where)
            ->whereRaw('month(tgl_sp2d) <='.$bulan)
            ->get(['no_spp','no_sp2d','tgl_sp2d','pengenal','uraian','nilairupiah']);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Nomor SPP')
            ->setCellValue('C1', 'Nomor SP2D')
            ->setCellValue('D1', 'Tanggal SP2D')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Uraian')
            ->setCellValue('G1', 'Nilai Rupiah');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasisaktibiro as $data) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->no_spp)
                ->setCellValue('C' . $kolom, $data->no_sp2dd)
                ->setCellValue('D' . $kolom, $data->tgl_sp2d)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->uraian)
                ->setCellValue('G' . $kolom, $data->nilairupiah);
            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data Realisasi SAKTI Biro '.$biro.' Satker '.$satker.' sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function tampildatasemarperbiro($idbiro, $satker){
        return view('ikpa.datarealisasisemarbiro',[
            'judul' => 'Data Realisasi Menurut SEMAR',
            'idbiro' => $idbiro,
            'satker'=> $satker
        ]);
    }

    public function datarealisasisemarperbiro(Request $request, $idbiro, $satker){
        if ($request->ajax()){
            //ambil bulan
            $tanggalserver = new DateTime();
            $bulan = $tanggalserver->format('n');
            $tahunanggaran = session('tahunanggaran');
            $where = array(
                'idbiro' => $idbiro,
                'kdsatker' => $satker
            );
            $datarealisasisaktibiro = RealisasiSemar::where($where)
                ->whereRaw('month(tanggal_kwitansi_karwas) <='.$bulan)
                ->whereRaw('year(tanggal_kwitansi_karwas) = '.$tahunanggaran)
                ->get(['tanggal_kwitansi_karwas','no_spp','no_spby','pengenal','uraian_pekerjaan','nilai_tagihan']);
            return Datatables::of($datarealisasisaktibiro)
                ->addIndexColumn()

                ->addColumn('nilai_tagihan',function($row){
                    $nilairupiah = $row->nilai_tagihan;
                    $nilairupiah = number_format($nilairupiah,0,',','.');
                    return $nilairupiah;

                })
                ->make(true);
        }

    }

    public function exportdatasemarbiro($kdsatker, $idbiro){
        $tanggalserver = new DateTime();
        $bulan = $tanggalserver->format('n');
        $tahunanggaran = session('tahunanggaran');
        $where = array(
            'idbiro' => $idbiro,
            'kdsatker' => $kdsatker
        );
        $datarealisasisaktibiro = RealisasiSemar::where($where)
            ->whereRaw('month(tanggal_kwitansi_karwas) <='.$bulan)
            ->whereRaw('year(tanggal_kwitansi_karwas) = '.$tahunanggaran)
            ->get(['tanggal_kwitansi_karwas','no_spp','no_spby','pengenal','uraian_pekerjaan','nilai_tagihan']);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Tanggal Karwas')
            ->setCellValue('C1', 'Nomor SPP')
            ->setCellValue('D1', 'No SPBy')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Uraian Pekerjaan')
            ->setCellValue('G1', 'Nilai Tagihan');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasisaktibiro as $data) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->tanggal_kwitansi_karwas)
                ->setCellValue('C' . $kolom, $data->no_spp)
                ->setCellValue('D' . $kolom, $data->no_spby)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->uraian_pekerjaan)
                ->setCellValue('G' . $kolom, $data->nilai_tagihan);
            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data Realisasi SEMAR Biro '.$idbiro.' Satker '.$kdsatker.' sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function tampildatasaktibagian($idbiro, $idbagian, $satker){
        $uraianbagian = Bagian::where('id','=',$idbagian)->value('uraianbagian');

        return view('ikpa.datarealisasisaktibagian',[
            'judul' => 'Data Realisasi Menurut SAKTI',
            'idbiro' => $idbiro,
            'idbagian' => $idbagian,
            'satker' => $satker,
            'uraianbagian' => $uraianbagian
        ]);
    }

    public function getdatasaktibagian(Request $request, $idbagian, $satker){
        if ($request->ajax()){
            //ambil bulan
            $tanggalserver = new DateTime();
            $bulan = $tanggalserver->format('n');
            $tahunanggaran = session('tahunanggaran');

            $where = array(
                'idbagian' => $idbagian,
                'kdsatker' => $satker,
                'tahunanggaran' => $tahunanggaran
            );
            $datarealisasisaktibagian = Realisasi::where($where)
                ->whereRaw('month(tgl_sp2d) <='.$bulan)
                ->get(['no_spp','no_sp2d','tgl_sp2d','pengenal','uraian','nilairupiah']);
            return Datatables::of($datarealisasisaktibagian)
                ->addIndexColumn()

                ->addColumn('nilairupiah',function($row){
                    $nilairupiah = $row->nilairupiah;
                    $nilairupiah = number_format($nilairupiah,0,',','.');
                    return $nilairupiah;

                })
                ->make(true);
        }

    }

    public function exportdatasaktibagian($satker, $idbagian){
        $uraianbagian = Bagian::where('id','=',$idbagian)->value('uraianbagian');
        //ambil bulan
        $tanggalserver = new DateTime();
        $bulan = $tanggalserver->format('n');
        $tahunanggaran = session('tahunanggaran');

        $where = array(
            'idbagian' => $idbagian,
            'kdsatker' => $satker,
            'tahunanggaran' => $tahunanggaran
        );
        $datarealisasisaktibagian = Realisasi::where($where)
            ->whereRaw('month(tgl_sp2d) <='.$bulan)
            ->get(['no_spp','no_sp2d','tgl_sp2d','pengenal','uraian','nilairupiah']);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Nomor SPP')
            ->setCellValue('C1', 'Nomor SP2D')
            ->setCellValue('D1', 'Tanggal SP2D')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Uraian')
            ->setCellValue('G1', 'Nilai Rupiah');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasisaktibagian as $data) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->no_spp)
                ->setCellValue('C' . $kolom, $data->no_sp2dd)
                ->setCellValue('D' . $kolom, $data->tgl_sp2d)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->uraian)
                ->setCellValue('G' . $kolom, $data->nilairupiah);
            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data Realisasi SAKTI Bagian '.$uraianbagian.' Satker '.$satker.' sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }

    public function tampildatasemarbagian($idbiro, $idbagian, $satker){
        $uraianbagian = Bagian::where('id','=',$idbagian)->value('uraianbagian');

        return view('ikpa.datarealisasisemarbagian',[
            'judul' => 'Data Realisasi Menurut Semar',
            'idbiro' => $idbiro,
            'idbagian' => $idbagian,
            'satker' => $satker,
            'uraianbagian' => $uraianbagian
        ]);
    }

    public function getdatasemarbagian(Request $request, $idbagian, $satker){
        if ($request->ajax()){
            //ambil bulan
            $tanggalserver = new DateTime();
            $bulan = $tanggalserver->format('n');
            $tahunanggaran = session('tahunanggaran');
            $where = array(
                'idbagian' => $idbagian,
                'kdsatker' => $satker
            );
            $datarealisasisemarbagian = RealisasiSemar::where($where)
                ->whereRaw('month(tanggal_kwitansi_karwas) <='.$bulan)
                ->whereRaw('year(tanggal_kwitansi_karwas) = '.$tahunanggaran)
                ->get(['tanggal_kwitansi_karwas','no_spp','no_spby','pengenal','uraian_pekerjaan','nilai_tagihan']);
            return Datatables::of($datarealisasisemarbagian)
                ->addIndexColumn()

                ->addColumn('nilai_tagihan',function($row){
                    $nilairupiah = $row->nilai_tagihan;
                    $nilairupiah = number_format($nilairupiah,0,',','.');
                    return $nilairupiah;

                })
                ->make(true);
        }

    }

    public function exportdatasemarbagian($kdsatker, $idbagian){
        $uraianbagian = Bagian::where('id','=',$idbagian)->value('uraianbagian');
        $tanggalserver = new DateTime();
        $bulan = $tanggalserver->format('n');
        $tahunanggaran = session('tahunanggaran');
        $where = array(
            'idbagian' => $idbagian,
            'kdsatker' => $kdsatker
        );
        $datarealisasisaktibagian = RealisasiSemar::where($where)
            ->whereRaw('month(tanggal_kwitansi_karwas) <='.$bulan)
            ->whereRaw('year(tanggal_kwitansi_karwas) = '.$tahunanggaran)
            ->get(['tanggal_kwitansi_karwas','no_spp','no_spby','pengenal','uraian_pekerjaan','nilai_tagihan']);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Nomor')
            ->setCellValue('B1', 'Tanggal Karwas')
            ->setCellValue('C1', 'Nomor SPP')
            ->setCellValue('D1', 'No SPBy')
            ->setCellValue('E1', 'Pengenal')
            ->setCellValue('F1', 'Uraian Pekerjaan')
            ->setCellValue('G1', 'Nilai Tagihan');

        $kolom = 2;
        $nomor = 1;
        foreach($datarealisasisaktibagian as $data) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $kolom, $nomor)
                ->setCellValue('B' . $kolom, $data->tanggal_kwitansi_karwas)
                ->setCellValue('C' . $kolom, $data->no_spp)
                ->setCellValue('D' . $kolom, $data->no_spby)
                ->setCellValue('E' . $kolom, $data->pengenal)
                ->setCellValue('F' . $kolom, $data->uraian_pekerjaan)
                ->setCellValue('G' . $kolom, $data->nilai_tagihan);
            $kolom++;
            $nomor++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data Realisasi SEMAR Bagian '.$uraianbagian.' Satker '.$kdsatker.' sd Bulan '.$bulan;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        die();
    }
}
