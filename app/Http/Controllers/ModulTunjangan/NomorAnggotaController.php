<?php

namespace App\Http\Controllers\ModulTunjangan;

use App\Http\Controllers\Controller;
use App\Models\ModulTunjangan\NomorAnggota;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Http\Request;

class NomorAnggotaController extends Controller
{
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

    private function _showOutput($judul, $lokasi, $output) {
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
            'judul' => $judul
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

    public function kelolanomoranggota()
    {
        $lokasi = 'ModulTunjangan.nomoranggota';
        $judul = 'List Nomor Anggota';
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setTable('nomoranggota');
        $crud->displayAs('nomoranggota','Nomor Anggota');
        $crud->setSkin('bootstrap-v3');
        $crud->defaultOrdering('id');
        $output = $crud->render();
        return $this->_showOutput($judul,$lokasi,$output);
    }

    public function isinomoranggota(){
        for ($i = 1;$i<=575;$i++){
            $nomoranggota = 'A-'.$i;
            $cekadadata = NomorAnggota::where('nomoranggota','=',$nomoranggota)->get()->count();
            if ($cekadadata < 1){
                $datainsert = array(
                    'nomoranggota' => 'A-'.$i
                );
                NomorAnggota::insert($datainsert);
            }
        }
        return redirect()->to('Anggota.nomoranggota')->with('berhasil','Isi Nomor Anggota Berhasil');
    }
}
