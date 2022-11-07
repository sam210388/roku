<?php

namespace App\Http\Controllers\AdministrasiAplikasi;

use App\Http\Controllers\Controller;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Http\Request;
use App\Models\AdministrasiAplikasi\BearerKey;

class BearerKeyController extends Controller
{
    public function dapatkanbearerkey(){
        $tahunanggaran = session('tahunanggaran');

        $bearerkey = BearerKey::where('tahunanggaran',$tahunanggaran)->get()->first();
        $bearerkey = $bearerkey->bearerkey;
        return $bearerkey;
    }

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

    public function bearerkey()
    {
        $lokasi = 'AdministrasiAplikasi.bearerkey';
        $judul = 'Daftar BearerKey';
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setTable('bearerkey');
        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();
        return $this->_showOutput($judul,$lokasi,$output);
    }
}
