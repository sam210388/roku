<?php
// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers\AdministrasiAplikasi;

use App\Models\User;
use App\Models\AdministrasiAplikasi\UserModel;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class KelolaUserController extends Controller
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

    public function users()
    {
        $lokasi = 'AdministrasiAplikasi.users';
        $judul = 'Kelola User';
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setTable('users');
        $crud->setSubject('User', 'User');
        $crud->callbackBeforeInsert(array($this,'enkripsipassword'));
        $crud->callbackBeforeUpdate(array($this,'enkripsipassword'));
        $crud->setRelation('role_id','roles','{id} {name}');

        $crud->setSkin('bootstrap-v3');
        $output = $crud->render();

        return $this->_showOutput($judul,$lokasi,$output);
    }

    function enkripsipassword($post_array){
        if (isset($post_array->primaryKeyValue)){

            //berarti update
            //dapatkan password di data
            $iduser = $post_array->primaryKeyValue;
            $passwordform = $post_array->data['password'];
            $datauser = UserModel::find($iduser);
            $datapassword = $datauser->password;

            if ($datapassword != $passwordform){
                $passwordbaru = Hash::make($passwordform);
                $post_array->data['password'] = $passwordbaru;
                return $post_array;
            }else{
                return $post_array;
            }
        }else{
            $password = Hash::make(($post_array->data['password']));
            $post_array->data['password'] = $password;
            return $post_array;
        }
    }

}
