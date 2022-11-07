<?php

namespace App\Http\Controllers\ModulTunjangan;

use App\Http\Controllers\Controller;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Http\Request;

class NamaAnggotaController extends Controller
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

    public function namaanggota()
    {
        $lokasi = 'ModulTunjangan.namaanggota';
        $judul = 'List Nama Anggota';
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setTable('namaanggota');
        $crud->displayAs('namalengkap','Nama Lengkap');
        $crud->displayAs('alamatjakarta','Alamat Jakarta');
        $crud->displayAs('alamatdaerah','Alamat Daerah');
        $crud->displayAs('noktp','Nomor KTP');
        $crud->displayAs('npwp','NPWP');
        $crud->displayAs('nomorhp','No HP');
        $crud->displayAs('email','Email');
        $crud->setSkin('bootstrap-v3');
        $crud->defaultOrdering('id');
        $output = $crud->render();
        return $this->_showOutput($judul,$lokasi,$output);
    }
}
