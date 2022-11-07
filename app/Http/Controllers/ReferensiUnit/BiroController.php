<?php

namespace App\Http\Controllers\ReferensiUnit;

use App\Models\Bagian;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Models\Biro;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BiroController extends Controller
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

    public function biro()
    {
        $lokasi = 'ReferensiUnit.biro';
        $judul = 'Daftar Biro';
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setTable('biro');
        $crud->setSubject('Biro', 'Biro');
        $crud->columns(['kdsatker','iddeputi','uraianbiro']);
        $crud->displayAs('uraianbiro','Uraian Biro');
        $crud->displayAs('kdsatker','Satker');
        $crud->displayAs('iddeputi','Deputi');
        $crud->setRelation('iddeputi','deputi','uraiandeputi');
        $crud->callbackBeforeDelete(array($this, 'cekpenggunaanbiro'));
        $crud->setPrimaryKey('idbiro', 'biro');
        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output);
    }

    public function cekpenggunaanbiro($post_array){
        $idbiro = $post_array->primaryKeyValue;
        $databiro = DB::table('bagian')->where('idbiro',$idbiro)->first();
        if ($databiro){
            $errorMessage = new \GroceryCrud\Core\Error\ErrorMessage();
            return $errorMessage->setMessage("Tidak Bisa Delete Biro ini, Sudah Digunakan pada Bagian.");
        }
        else{
            return $post_array;
        }
    }


}
