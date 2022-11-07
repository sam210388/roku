<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\AnggaranBagian;
use App\Models\AnggaranRealisasi\DataAng;
use App\Models\ReferensiUnit\Bagian;
use App\Models\ReferensiUnit\Biro;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Http\Request;

class AnggaranBagianController extends Controller
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
        $infosetjen = $info['infosetjen'];
        $infodewan = $info['infodewan'];

        return view($lokasi, [
            'output' => $output,
            'css_files' => $css_files,
            'js_files' => $js_files,
            'judul' => $judul,
            'infosetjen' => $infosetjen,
            'infodewan' => $infodewan
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

    public function anggaranbagian()
    {

        $tahunanggaran = session('tahunanggaran');
        $dataanaksatker = Biro::all();
        $lokasi = 'AnggaranRealisasi.anggaranbagian';
        $judul = 'Kelola Anggaran Bagian';
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->where('tahunanggaran ='.$tahunanggaran);
        $crud->setTable('anggaranbagian');
        $crud->setSubject('Anggaran', 'Anggaran');
        $crud->columns(['tahunanggaran','kdsatker','kodekegiatan','kodeoutput','kodesuboutput','kodekomponen','kodesubkomponen','kodeakun','pengenal','idrefstatus','idbagian']);
        $crud->displayAs('tahunanggaran','Tahun Anggaran');
        $crud->displayAs('kdsatker','Satker');
        $crud->displayAs('kodekegiatan','Kegiatan');
        $crud->displayAs('kodeoutput','Output');
        $crud->displayAs('kodesuboutput','Sub Output');
        $crud->displayAs('kodekomponen','Komponen');
        $crud->displayAs('kodesubkomponen','SubKomponen');
        $crud->displayAs('kodeakun','Akun');
        $crud->displayAs('pengenal','Pengenal');
        $crud->displayAs('idbagian','Bagian');
        $crud->setRelation('idbagian','bagian','{uraianbagian}');

        $crud->setActionButtonMultiple('Tambah Anggaran','fa fa-ok','anggaran/tambahanggaran');
        $totalDataSetjen = AnggaranBagian::where('tahunanggaran','=',$tahunanggaran)->where('kdsatker','=','001012')->get()->count();
        $dataTerisiSetjen = AnggaranBagian::where('idbagian','!=','')->where('kdsatker','=','001012')->get()->count();
        $infosetjen = "Satker Setjen Telah terisi ".$dataTerisiSetjen.' dari Total Data '.$totalDataSetjen;

        $totalDataDewan = AnggaranBagian::where('tahunanggaran','=',$tahunanggaran)->where('kdsatker','=','001030')->get()->count();
        $dataTerisiDewan = AnggaranBagian::where('idbagian','!=','')->where('kdsatker','=','001030')->get()->count();
        $infodewan = "Satker Dewan Telah terisi ".$dataTerisiDewan.' dari Total Data '.$totalDataDewan;
        $info = array(
            'infosetjen' => $infosetjen,
            'infodewan' => $infodewan
        );

        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output, $info);
    }

    public function tambahanggaran($idbagian)
    {
        $tahunanggaran = session('tahunanggaran');
        $dataanaksatker = Biro::all();
        $lokasi = 'AnggaranRealisasi.anggaranbagian';
        $judul = 'Kelola Anggaran Bagian';
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->where('tahunanggaran ='.$tahunanggaran);
        $crud->setTable('anggaranbagian');
        $crud->setSubject('Anggaran', 'Anggaran');
        $crud->columns(['kodeprogram','kodekegiatan','kodeoutput','kodesuboutput','kodekomponen','kodesubkomponen','kodeakun','pengenal','idrefstatus','idbagian']);
        $crud->displayAs('kodeprogram','Program');
        $crud->displayAs('kodekegiatan','Kegiatan');
        $crud->displayAs('kodeoutput','Output');
        $crud->displayAs('kodesuboutput','Sub Output');
        $crud->displayAs('kodekomponen','Komponen');
        $crud->displayAs('kodesubkomponen','SubKomponen');
        $crud->displayAs('kodeakun','Akun');
        $crud->displayAs('pengenal','Pengenal');
        $crud->displayAs('idbagian','Bagian');
        $crud->setRelation('idbagian','bagian','{uraianbagian}');

        $crud->setActionButtonMultiple('Tambah Anggaran','fa fa-ok','/anggaran/alokasianggaranbagian/'.$idbagian);

        $totalDataSetjen = AnggaranBagian::where('tahunanggaran','=',$tahunanggaran)->where('kdsatker','=','001012')->get()->count();
        $dataTerisiSetjen = AnggaranBagian::where('idbagian','!=','')->where('kdsatker','=','001012')->get()->count();
        $infosetjen = "Satker Setjen Telah terisi ".$dataTerisiSetjen.' dari Total Data '.$totalDataSetjen;

        $totalDataDewan = AnggaranBagian::where('tahunanggaran','=',$tahunanggaran)->where('kdsatker','=','001030')->get()->count();
        $dataTerisiDewan = AnggaranBagian::where('idbagian','!=','')->where('kdsatker','=','001030')->get()->count();
        $infodewan = "Satker Dewan Telah terisi ".$dataTerisiDewan.' dari Total Data '.$totalDataDewan;
        $info = array(
            'infosetjen' => $infosetjen,
            'infodewan' => $infodewan
        );

        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output, $info);
    }

    public function alokasianggaranbagian($idbagian, Request $request){
        $idbagian = $idbagian;
        $idbiro = Bagian::where('id','=',$idbagian)->value('idbiro');
        $iddeputi = Bagian::where('id','=',$idbagian)->value('iddeputi');
        $uraianbagian = Bagian::where('id','=',$idbagian)->get('uraianbagian');
        $idanggaranbagian = $request->id;
        foreach ($idanggaranbagian as $item){
            $datawhere = array(
                'id' => $item
            );

            $dataupdate = array(
                'idbagian' => $idbagian,
                'idbiro' => $idbiro,
                'iddeputi' => $iddeputi
            );

            AnggaranBagian::where($datawhere)->update($dataupdate);
        }
        return redirect()->to('anggaran/tambahanggaran/'.$idbagian)->with('alokasiberhasil','Alokasi Anggaran Ke Bagian '.$uraianbagian.' Berhasil');


    }
}
