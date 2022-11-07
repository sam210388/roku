<?php

namespace App\Libraries;

class PeriodeLaporan {
    public function tanggallaporan($tahunanggaran, $periodesasi, $bulan){
        //menentukan tahun apakah kabisat atau bukan
        if (intval($tahunanggaran) / 4 == 0){
            $tahunkabisat = true;
        }else{
            $tahunkabisat = false;
        }
        //menentukan format bulan
        switch ($bulan){
            case ($bulan < 10):
                $formatbulan = '0'.$bulan;
                break;
            default:
                $formatbulan = $bulan;
        }

        //menentukan tanggal awal dan akhir laporan

        $tanggalawal = '01';
        switch ($bulan){
            case ($bulan % 2 == 0 and $bulan != 2):
                $tanggalakhir = '30';
                break;
            case ($bulan == 2 and $tahunkabisat == true):
                $tanggalakhir = '29';
                break;
            case ($bulan == 2 and $tahunkabisat == false):
                $tanggalakhir = '28';
                break;
            default:
                $tanggalakhir = '31';
        }

        //mengembalikan nilai sesuai pilihan periodesasi dimana 1 itu sd 2 itu bulan bersangkutan
        if ($periodesasi == 1){
            $periodeawal = $tahunanggaran.'-01-01';
            $periodeakhir = $tahunanggaran.'-'.$formatbulan.'-'.$tanggalakhir;
            $periode = array(
                'tanggal_awal' => $periodeawal,
                'tanggal_akhir' => $periodeakhir
            );
            return $periode;
        }else{
            $periodeawal = $tahunanggaran.'-'.$formatbulan.'01';
            $periodeakhir = $tahunanggaran.'-'.$formatbulan.'-'.$tanggalakhir;
            $periode = array(
                'tanggal_awal' => $periodeawal,
                'tanggal_akhir' => $periodeakhir
            );
            return $periode;
        }
    }

}
