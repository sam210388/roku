<?php

namespace App\Http\Controllers\AnggaranRealisasi;

use App\Http\Controllers\AdministrasiAplikasi\BearerKeyController;
use App\Http\Controllers\Controller;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Http\Request;
use App\Models\AnggaranRealisasi\Program;

class ProgramController extends Controller
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

    public function program()
    {

        $lokasi = 'AnggaranRealisasi.program';
        $judul = 'Daftar Program';
        $crud = $this->_getGroceryCrudEnterprise();
        $tahunanggaran = session('tahunanggaran');
        $crud->where('thang = '.$tahunanggaran);
        $crud->setTable('program');
        $crud->setSubject('Program', 'Program');
        $crud->displayAs('thang','Tahun Anggaran');
        $crud->displayAs('kode','Kode');
        $crud->displayAs('deskripsi','Deskripsi');
        $crud->unsetAdd();
        $crud->unsetDeleteMultiple();
        $crud->unsetDelete();
        $crud->unsetEdit();

        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output);
    }

    function importprogram(){
        //tarik data dari monsakti
        $bearerkey = new BearerKeyController();
        $bearerkey = $bearerkey->dapatkanbearerkey();
        $key = $bearerkey;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://monsakti.kemenkeu.go.id/sitp-monsakti-omspan/webservice/API/KL002/refUraian/program',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $diolah = substr(json_encode($response),10,14);
        if ($diolah !== "" && $diolah !== "<b>Fatal error" ) {
            $hasilasli = json_decode($response);
            foreach ($hasilasli as $ITEM) {
                $THANG = $ITEM->THANG;
                $KODE = $ITEM->KODE;
                $DESKRIPSI = $ITEM->DESKRIPSI;

                $where = array(
                    'thang' => $THANG,
                    'kode' => $KODE,
                    'deskripsi' => $DESKRIPSI
                );

                $jumlah = Program::where($where)->get()->count();
                if ($jumlah == 0){
                    $data = array(
                        'thang' => $THANG,
                        'kode' => $KODE,
                        'deskripsi' => $DESKRIPSI
                    );
                    Program::insert($data);
                }
            }
            return redirect()->to('anggaran/program')->with('status','Import Program Berhasil');
        }
    }
}
