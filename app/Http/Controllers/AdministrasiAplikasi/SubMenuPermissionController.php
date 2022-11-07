<?php

namespace App\Http\Controllers\AdministrasiAplikasi;

use App\Http\Controllers\Controller;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Http\Request;
use App\Models\AdministrasiAplikasi\SubMenuPermission;
use Illuminate\Support\Facades\DB;

class SubMenuPermissionController extends Controller
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

    public function submenupermission()
    {
        $lokasi = 'AdministrasiAplikasi.SubMenuPermission';
        $judul = 'Kelola Sub Menu';
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setTable('submenupermission');
        $crud->setSubject('SubMenu-Permission', 'SubMenu-Permissions');
        $crud->displayAs('idmenu','Menu');
        $crud->displayAs('permissionname','Permission');
        $crud->displayAs('textsubmenu','Tampilan Menu');

        $crud->setRelation('idmenu','menu','{teksmenu}');
        $crud->setRelation('permissionname','permissions','{name}');

        $crud->callbackBeforeInsert(array($this,'ubahnamepermission'));
        $crud->callbackBeforeUpdate(array($this,'ubahnamepermission'));

        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output);
    }

    public function ubahnamepermission($post_array){
        $idpermission = $post_array->data['permissionname'];

        $datapermission = DB::table('permissions')->where('id',$idpermission)->get()->first();
        $namepermission = $datapermission->name;
        $post_array->data['permissionname'] = $namepermission;
        return $post_array;

    }
}
