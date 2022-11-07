<?php

namespace App\Http\Controllers\ReferensiUnit;

use App\Models\AnggaranRealisasi\AnggaranBagian;
use App\Models\ReferensiUnit\Biro;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BagianController extends Controller
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

    public function bagian()
    {
        $lokasi = 'ReferensiUnit.bagian';
        $judul = 'Daftar Bagian';
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setTable('bagian');
        $crud->setSubject('Bagian', 'Bagian');
        $crud->displayAs('uraianbagian','Uraian Bagian');
        $crud->displayAs('iddeputi','Deputi');
        $crud->displayAs('idbiro','Biro');
        $crud->callbackColumn('idbagian',array($this,'geturaianbiro'));
        $crud->setRelation('iddeputi','deputi','uraiandeputi');
        //$crud->setRelation('idbiro','biro','uraianbiro');
        $crud->callbackBeforeDelete(array($this, 'cekpenggunaanbagian'));
        $crud->setActionButton('Tambah Anggaran','fa fa-list',array($this,'tambahanggaran'));

        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output);
    }

    public function geturaianbiro($value){
        $uraianbiro = Biro::where('idbiro','=',$value)->value('uraianbiro');
        return $uraianbiro;
    }

    public function tambahanggaran($row){
        $idbagian = $row->id;
        return 'tambahanggaran/'.$idbagian;
    }

    public function cekpenggunaanbagian($post_array){
        $idbagian = $post_array->primaryKeyValue;

        $databagian = AnggaranBagian::where('idbagian','=',$idbagian)->get()->first();
        if ($databagian){
            $errorMessage = new \GroceryCrud\Core\Error\ErrorMessage();
            return $errorMessage->setMessage("Tidak Bisa Delete Bagian ini, Sudah Pernah Digunakan pada Anggaran Bagian.");
        }
        else{
            return $post_array;
        }
    }
}
