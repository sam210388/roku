<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdministrasiAplikasi\KelolaUserController;
use App\Http\Controllers\AdministrasiAplikasi\RolesController;
use App\Http\Controllers\ReferensiUnit\DeputiController;
use App\Http\Controllers\ReferensiUnit\BiroController;
use App\Http\Controllers\ReferensiUnit\BagianController;
use App\Http\Controllers\AnggaranRealisasi\RefStatusController;
use App\Http\Controllers\AnggaranRealisasi\dataAngController;
use App\Http\Controllers\AnggaranRealisasi\RealisasiController;
use App\Http\Controllers\AnggaranRealisasi\ProgramController;
use App\Http\Controllers\AnggaranRealisasi\KegiatanController;
use App\Http\Controllers\AdministrasiAplikasi\BearerKeyController;
use App\Http\Controllers\AnggaranRealisasi\OutputController;
use App\Http\Controllers\AnggaranRealisasi\SuboutputController;
use App\Http\Controllers\AnggaranRealisasi\AkunController;
use App\Http\Controllers\AdministrasiAplikasi\MenuPermissionController;
use App\Http\Controllers\AdministrasiAplikasi\SubMenuPermissionController;
use App\Http\Controllers\AdministrasiAplikasi\KelolaAksesController;
use App\Http\Controllers\AnggaranRealisasi\AnggaranBagianController;
use App\Http\Controllers\AnggaranRealisasi\ExportRealisasiController;
use App\Http\Controllers\ModulTunjangan\NomorAnggotaController;
use App\Http\Controllers\ModulTunjangan\FraksiController;
use App\Http\Controllers\ModulTunjangan\NamaAnggotaController;
use App\Http\Controllers\AnggaranRealisasi\KomponenController;
use App\Http\Controllers\Ikpa\RealisasiBiroController;
use App\Http\Controllers\Ikpa\DataRealisasiController;
use App\Http\Controllers\Ikpa\PerhitunganIkpaBulanan;
use App\Http\Controllers\PIPK\AkunSignifikanController;
use App\Http\Controllers\PIPK\JenisDokumenTagihanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();


Route::match(["GET", "POST"], "/register", function(){
    return redirect("/login");
})->name("register");

Route::get('/', function (){
    return redirect(("/login"));
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group([
    'middleware' => ['auth','auth.role'],
    'prefix' => 'anggaran',
    'role' => ['SuperAdmin','Anggaran'],
    'as' => 'anggaran.'
], function(){
    Route::get('deputi', [DeputiController::class, 'deputi'])->name('tampil-deputi');
    Route::get('deputi/{operation}', [DeputiController::class, 'deputi'])->name('tambah-deputi');
    Route::get('deputi/{operation}/{id}', [DeputiController::class, 'deputi'])->name('edit-deputi');
    Route::post('deputi', [DeputiController::class, 'deputi'])->name('save-deputi');
    Route::post('deputi/{operation}', [DeputiController::class, 'deputi'])->name('update-deputi');
    Route::post('deputi/{operation}/{id}', [DeputiController::class, 'deputi'])->name('delete-deputi');

    Route::get('biro', [BiroController::class, 'biro'])->name('tampil-biro');
    Route::get('biro/{operation}', [BiroController::class, 'biro'])->name('tambah-biro');
    Route::get('biro/{operation}/{id}', [BiroController::class, 'biro'])->name('edit-biro');
    Route::post('biro', [BiroController::class, 'biro'])->name('save-biro');
    Route::post('biro/{operation}', [BiroController::class, 'biro'])->name('update-biro');
    Route::post('biro/{operation}/{id}', [BiroController::class, 'biro'])->name('delete-biro');

    Route::get('bagian', [BagianController::class, 'bagian'])->name('tampil-bagian');
    Route::get('bagian/{operation}', [BagianController::class, 'bagian'])->name('tambah-bagian');
    Route::get('bagian/{operation}/{id}', [BagianController::class, 'bagian'])->name('edit-bagian');
    Route::post('bagian', [BagianController::class, 'bagian'])->name('save-bagian');
    Route::post('bagian/{operation}', [BagianController::class, 'bagian'])->name('update-bagian');
    Route::post('bagian/{operation}/{id}', [BagianController::class, 'bagian'])->name('delete-bagian');

    Route::get('program', [ProgramController::class, 'program'])->name('tampil-program');
    Route::get('program/{operation}', [ProgramController::class, 'program'])->name('tambah-program');
    Route::get('program/{operation}/{id}', [ProgramController::class, 'program'])->name('edit-program');
    Route::post('program', [ProgramController::class, 'program'])->name('save-program');
    Route::post('program/{operation}', [ProgramController::class, 'program'])->name('update-program');
    Route::post('program/{operation}/{id}', [ProgramController::class, 'program'])->name('delete-program');

    Route::get('kegiatan', [KegiatanController::class, 'kegiatan'])->name('tampil-kegiatan');
    Route::get('kegiatan/{operation}', [KegiatanController::class, 'kegiatan'])->name('tambah-kegiatan');
    Route::get('kegiatan/{operation}/{id}', [KegiatanController::class, 'kegiatan'])->name('edit-kegiatan');
    Route::post('kegiatan', [KegiatanController::class, 'kegiatan'])->name('save-kegiatan');
    Route::post('kegiatan/{operation}', [KegiatanController::class, 'kegiatan'])->name('update-kegiatan');
    Route::post('kegiatan/{operation}/{id}', [KegiatanController::class, 'kegiatan'])->name('delete-kegiatan');

    Route::get('output', [OutputController::class, 'output'])->name('tampil-output');
    Route::get('output/{operation}', [OutputController::class, 'output'])->name('tambah-output');
    Route::get('output/{operation}/{id}', [OutputController::class, 'output'])->name('edit-output');
    Route::post('output', [OutputController::class, 'output'])->name('save-output');
    Route::post('output/{operation}', [OutputController::class, 'output'])->name('update-output');
    Route::post('output/{operation}/{id}', [OutputController::class, 'output'])->name('delete-output');

    Route::get('suboutput', [SuboutputController::class, 'suboutput'])->name('tampil-suboutput');
    Route::get('suboutput/{operation}', [SuboutputController::class, 'suboutput'])->name('tambah-suboutput');
    Route::get('suboutput/{operation}/{id}', [SuboutputController::class, 'suboutput'])->name('edit-suboutput');
    Route::post('suboutput', [SuboutputController::class, 'suboutput'])->name('save-suboutput');
    Route::post('suboutput/{operation}', [SuboutputController::class, 'suboutput'])->name('update-suboutput');
    Route::post('suboutput/{operation}/{id}', [SuboutputController::class, 'suboutput'])->name('delete-suboutput');

    Route::get('komponen', [KomponenController::class, 'komponen'])->name('tampil-komponen');
    Route::get('komponen/{operation}', [KomponenController::class, 'komponen'])->name('tambah-komponen');
    Route::get('komponen/{operation}/{id}', [KomponenController::class, 'komponen'])->name('edit-komponen');
    Route::post('komponen', [KomponenController::class, 'komponen'])->name('save-komponen');
    Route::post('komponen/{operation}', [KomponenController::class, 'komponen'])->name('update-komponen');
    Route::post('komponen/{operation}/{id}', [KomponenController::class, 'komponen'])->name('delete-komponen');

    Route::get('akun', [AkunController::class, 'akun'])->name('tampil-akun');
    Route::get('akun/{operation}', [AkunController::class, 'akun'])->name('tambah-akun');
    Route::get('akun/{operation}/{id}', [AkunController::class, 'akun'])->name('edit-akun');
    Route::post('akun', [AkunController::class, 'akun'])->name('save-akun');
    Route::post('akun/{operation}', [AkunController::class, 'akun'])->name('update-akun');
    Route::post('akun/{operation}/{id}', [AkunController::class, 'akun'])->name('delete-akun');

    Route::get('realisasi', [RealisasiController::class, 'tampilrealisasi'])->name('tampil-tampilrealisasi');
    Route::get('realisasi/{operation}', [RealisasiController::class, 'tampilrealisasi'])->name('tambah-tampilrealisasi');
    Route::get('realisasi/{operation}/{id}', [RealisasiController::class, 'tampilrealisasi'])->name('edit-tampilrealisasi');
    Route::post('realisasi', [RealisasiController::class, 'tampilrealisasi'])->name('save-tampilrealisasi');
    Route::post('realisasi/{operation}', [RealisasiController::class, 'tampilrealisasi'])->name('update-tampilrealisasi');
    Route::post('realisasi/{operation}/{id}', [RealisasiController::class, 'tampilrealisasi'])->name('delete-tampilrealisasi');

    Route::get('anggaran', [dataAngController::class, 'tampilanggaran'])->name('tampil-anggaran');
    Route::get('anggaran/{$id}', [dataAngController::class, 'tampilanggaran'])->name('tampil-detilpagu');
    Route::get('anggaran/{operation}', [dataAngController::class, 'tampilanggaran'])->name('tambah-anggaran');
    Route::get('anggaran/{operation}/{id}', [dataAngController::class, 'tampilanggaran'])->name('edit-anggaran');
    Route::post('anggaran', [dataAngController::class, 'tampilanggaran'])->name('save-anggaran');
    Route::post('anggaran/{operation}', [dataAngController::class, 'tampilanggaran'])->name('update-anggaran');
    Route::post('anggaran/{operation}/{id}', [dataAngController::class, 'tampilanggaran'])->name('delete-anggaran');

    Route::get('refstatus', [RefStatusController::class, 'refstatus'])->name('tampil-refstatus');
    Route::get('datarefstatus', [RefStatusController::class, 'getRefstatusList'])->name('datarefstatus');
    Route::get('refstatus/{operation}', [RefStatusController::class, 'refstatus'])->name('tambah-refstatus');
    Route::get('refstatus/{operation}/{id}', [RefStatusController::class, 'refstatus'])->name('edit-refstatus');
    Route::post('refstatus', [RefStatusController::class, 'refstatus'])->name('save-roles');
    Route::post('refstatus/{operation}', [RefStatusController::class, 'refstatus'])->name('update-roles');
    Route::post('refstatus/{operation}/{id}', [RefStatusController::class, 'refstatus'])->name('delete-roles');

    Route::get('anggaranbagian', [AnggaranBagianController::class, 'anggaranbagian'])->name('tampil-anggaranbagian');
    Route::get('anggaranbagian/{operation}', [AnggaranBagianController::class, 'anggaranbagian'])->name('tambah-anggaranbagian');
    Route::get('anggaranbagian/{operation}/{id}', [AnggaranBagianController::class, 'anggaranbagian'])->name('edit-anggaranbagian');
    Route::post('anggaranbagian', [AnggaranBagianController::class, 'anggaranbagian'])->name('save-anggaranbagian');
    Route::post('anggaranbagian/{operation}', [AnggaranBagianController::class, 'anggaranbagian'])->name('update-anggaranbagian');
    Route::post('anggaranbagian/{operation}/{id}', [AnggaranBagianController::class, 'anggaranbagian'])->name('delete-anggaranbagian');

    Route::get('tambahanggaran/{idbagian}', [AnggaranBagianController::class, 'tambahanggaran'])->name('tambahanggaran');
    Route::post('tambahanggaran/{idbagian}', [AnggaranBagianController::class, 'tambahanggaran'])->name('tambahanggaran');

    Route::get('alokasianggaranbagian/{idbagian}', [AnggaranBagianController::class, 'alokasianggaranbagian'])->name('alokasianggaranbagian');
    Route::post('alokasianggaranbagian/{idbagian}', [AnggaranBagianController::class, 'alokasianggaranbagian'])->name('alokasianggaranbagian');

    Route::get('monitoringrealisasi', [ExportRealisasiController::class, 'monitorrealisasi'])->name('monitoringrealisasi');
    Route::post('monitoringrealisasi', [ExportRealisasiController::class, 'aksirekapdata'])->name('aksirekapmonitoringrealisasi');


    Route::get('importprogram/',[ProgramController::class,'importprogram'])->name('importprogram');
    Route::get('importkegiatan/',[KegiatanController::class,'importkegiatan'])->name('importkegiatan');
    Route::get('importoutput/',[OutputController::class,'importoutput'])->name('importoutput');
    Route::get('importsuboutput/',[SuboutputController::class,'importsuboutput'])->name('importsuboutput');
    Route::get('importkomponen/',[KomponenController::class,'importkomponen'])->name('importkomponen');
    Route::get('importakun/',[AkunController::class,'importakun'])->name('importakun');

    Route::get('importdataang/{idrefstatus}',[dataAngController::class,'importdataang'])->name('importdataang');
    Route::get('importdataangseluruh',[dataAngController::class,'importseluruhdata'])->name('importdataangseluruh');
    Route::get('rekapseluruhanggaran',[dataAngController::class,'rekapanggaranseluruh'])->name('rekapseluruhanggaran');
    Route::get('rekapanggaran/{idrefstatus}',[dataAngController::class,'rekapanggaran'])->name('rekapanggaran');
    Route::get('importrefstatus', [RefStatusController::class, 'importRefStatus'])->name('importrefstatus');
    Route::get('updatestatusimport',[dataAngController::class,'updatestatusimport'])->name('cekstatusimport');


    Route::get('importrealisasi/',[RealisasiController::class,'importrealisasi'])->name('importrealisasi');
    Route::get('alokasianggaran/',[dataAngController::class,'alokasiidbagiankeanggaran'])->name('alokasianggaran');
    Route::get('exportdataang/{idrefstatus}',[dataAngController::class,'exportdataanggaran'])->name('exportdataang');


});

    Route::get('importrealisasisemar',[RealisasiController::class,'importrealisasisemar'])->name('importrealisasisemar');

Route::group([
    'middleware' => ['auth','auth.role'],
    'prefix' => 'admin',
    'role' => ['SuperAdmin'],
    'as' => 'admin.'
], function(){
    Route::get('bearerkey', [BearerKeyController::class, 'bearerkey'])->name('tampil-bearerkey');
    Route::get('bearerkey/{operation}', [BearerKeyController::class, 'bearerkey'])->name('tambah-bearerkey');
    Route::get('bearerkey/{operation}/{id}', [BearerKeyController::class, 'bearerkey'])->name('edit-bearerkey');
    Route::post('bearerkey', [BearerKeyController::class, 'bearerkey'])->name('save-bearerkey');
    Route::post('bearerkey/{operation}', [BearerKeyController::class, 'bearerkey'])->name('update-bearerkey');
    Route::post('bearerkey/{operation}/{id}', [BearerKeyController::class, 'bearerkey'])->name('delete-bearerkey');

    Route::get('kelola-user', [KelolaUserController::class, 'users'])->name('tampil-user');
    Route::get('kelola-user/{operation}', [KelolaUserController::class, 'users'])->name('tambah-user');
    Route::get('kelola-user/{operation}/{id}', [KelolaUserController::class, 'users'])->name('edit-user');
    Route::post('kelola-user', [KelolaUserController::class, 'users'])->name('save-user');
    Route::post('kelola-user/{operation}', [KelolaUserController::class, 'users'])->name('update-user');
    Route::post('kelola-user/{operation}/{id}', [KelolaUserController::class, 'users'])->name('delete-user');

    Route::get('roles', [RolesController::class, 'roles'])->name('tampil-roles');
    Route::get('roles/{operation}', [RolesController::class, 'roles'])->name('tambah-roles');
    Route::get('roles/{operation}/{id}', [RolesController::class, 'roles'])->name('edit-roles');
    Route::post('roles', [RolesController::class, 'roles'])->name('save-roles');
    Route::post('roles/{operation}', [RolesController::class, 'roles'])->name('update-roles');
    Route::post('roles/{operation}/{id}', [RolesController::class, 'roles'])->name('delete-roles');

    Route::get('menu', [MenuPermissionController::class, 'menupermission'])->name('tampil-menu');
    Route::get('menu/{operation}', [MenuPermissionController::class, 'menupermission'])->name('tambah-menu');
    Route::get('menu/{operation}/{id}', [MenuPermissionController::class, 'menupermission'])->name('edit-menu');
    Route::post('menu', [MenuPermissionController::class, 'menupermission'])->name('save-menu');
    Route::post('menu/{operation}', [MenuPermissionController::class, 'menupermission'])->name('update-menu');
    Route::post('menu/{operation}/{id}', [MenuPermissionController::class, 'menupermission'])->name('delete-menu');

    Route::get('submenu-permission', [SubMenuPermissionController::class, 'submenupermission'])->name('tampil-submenupermission');
    Route::get('submenu-permission/{operation}', [SubMenuPermissionController::class, 'submenupermission'])->name('tambah-submenupermission');
    Route::get('submenu-permission/{operation}/{id}', [SubMenuPermissionController::class, 'submenupermission'])->name('edit-submenupermission');
    Route::post('submenu-permission', [SubMenuPermissionController::class, 'submenupermission'])->name('save-submenupermission');
    Route::post('submenu-permission/{operation}', [SubMenuPermissionController::class, 'submenupermission'])->name('update-submenupermission');
    Route::post('submenu-permission/{operation}/{id}', [SubMenuPermissionController::class, 'submenupermission'])->name('delete-submenupermission');

    Route::get('kelola-akses', [KelolaAksesController::class, 'kelolaakses'])->name('tampil-kelolaakses');
    Route::get('kelola-akses/{operation}', [KelolaAksesController::class, 'kelolaakses'])->name('tambah-kelolaakses');
    Route::get('kelola-akses/{operation}/{id}', [KelolaAksesController::class, 'kelolaakses'])->name('edit-kelolaakses');
    Route::post('kelola-akses', [KelolaAksesController::class, 'kelolaakses'])->name('save-kelolaakses');
    Route::post('kelola-akses/{operation}', [KelolaAksesController::class, 'kelolaakses'])->name('update-kelolaakses');
    Route::post('kelola-akses/{operation}/{id}', [KelolaAksesController::class, 'kelolaakses'])->name('delete-kelolaakses');

});

Route::group([
    'middleware' => ['auth','auth.role'],
    'prefix' => 'anggota',
    'role' => ['SuperAdmin','AdminAnggota'],
    'as' => 'Anggota.'
], function(){
    Route::get('nomoranggota', [NomorAnggotaController::class, 'kelolanomoranggota'])->name('nomoranggota');
    Route::get('nomoranggota/{operation}', [NomorAnggotaController::class, 'kelolanomoranggota'])->name('tambah-nomoranggota');
    Route::get('nomoranggota/{operation}/{id}', [NomorAnggotaController::class, 'kelolanomoranggota'])->name('edit-nomoranggota');
    Route::post('nomoranggota', [NomorAnggotaController::class, 'kelolanomoranggota'])->name('save-nomoranggota');
    Route::post('nomoranggota/{operation}', [NomorAnggotaController::class, 'kelolanomoranggota'])->name('update-nomoranggota');
    Route::post('nomoranggota/{operation}/{id}', [NomorAnggotaController::class, 'kelolanomoranggota'])->name('delete-nomoranggota');

    Route::get('isinomoranggota',[NomorAnggotaController::class,'isinomoranggota'])->name('isinomoranggota');

    Route::get('fraksi', [FraksiController::class, 'kelolafraksi'])->name('fraksi');
    Route::get('fraksi/{operation}', [FraksiController::class, 'kelolafraksi'])->name('tambah-fraksi');
    Route::get('fraksi/{operation}/{id}', [FraksiController::class, 'kelolafraksi'])->name('edit-fraksi');
    Route::post('fraksi', [FraksiController::class, 'kelolafraksi'])->name('save-fraksi');
    Route::post('fraksi/{operation}', [FraksiController::class, 'kelolafraksi'])->name('update-fraksi');
    Route::post('fraksi/{operation}/{id}', [FraksiController::class, 'kelolafraksi'])->name('delete-fraksi');

    Route::get('namaanggota', [NamaAnggotaController::class, 'namaanggota'])->name('namaanggota');
    Route::get('namaanggota/{operation}', [NamaAnggotaController::class, 'namaanggota'])->name('tambah-namaanggota');
    Route::get('namaanggota/{operation}/{id}', [NamaAnggotaController::class, 'namaanggota'])->name('edit-namaanggota');
    Route::post('namaanggota', [NamaAnggotaController::class, 'namaanggota'])->name('save-namaanggota');
    Route::post('namaanggota/{operation}', [NamaAnggotaController::class, 'namaanggota'])->name('update-namaanggota');
    Route::post('namaanggota/{operation}/{id}', [NamaAnggotaController::class, 'namaanggota'])->name('delete-namaanggota');
});

Route::group([
    'middleware' => ['auth','auth.role'],
    'prefix' => 'ikpa',
    'role' => ['SuperAdmin','AdminIKPA'],
    'as' => 'ikpa.'
], function(){
    Route::get('realisasiperbiro', [RealisasiBiroController::class, 'realisasibiro'])->name('tampilrealisasibiro');
    Route::get('realisasibiro', [RealisasiBiroController::class, 'getRealisasiPerBiro'])->name('realisasibiro');
    Route::get('tampildatasakti/{id}/{satker}', [DataRealisasiController::class, 'tampildataperbiro'])->name('tampildatasakti');
    Route::get('datasaktiperbiro/{id}/{satker}', [DataRealisasiController::class, 'datarealisasisaktiperbiro'])->name('datasaktiperbiro');
    Route::get('tampildatasemar/{id}/{satker}', [DataRealisasiController::class, 'tampildatasemarperbiro'])->name('tampildatasemar');
    Route::get('datasemarperbiro/{id}/{satker}', [DataRealisasiController::class, 'datarealisasisemarperbiro'])->name('datasemarperbiro');
    Route::get('datasaktibiro/{kdsatker}/{id}',[DataRealisasiController::class,'exportdatasaktibiro'])->name('exportdatasaktibiro');
    Route::get('datasemarbiro/{kdsatker}/{id}',[DataRealisasiController::class,'exportdatasemarbiro'])->name('exportdatasemarbiro');

    Route::get('tampilrealisasibagian/{idbiro}/{kdsatker}',[RealisasiBiroController::class,'tampilrealisasibagian'])->name('tampilrealisasibagian');
    Route::get('getdatarealisasibagian/{idbiro}/{kdsatker}',[RealisasiBiroController::class,'getdatarealisasibagian'])->name('getdatarealisasibagian');

    Route::get('tampildatasaktibagian/{idbiro}/{idbagian}/{satker}', [DataRealisasiController::class, 'tampildatasaktibagian'])->name('tampildatasaktibagian');
    Route::get('getdatasaktibagian/{id}/{satker}', [DataRealisasiController::class, 'getdatasaktibagian'])->name('getdatasaktibagian');
    Route::get('tampildatasemarbagian/{idbiro}/{idbagian}/{satker}', [DataRealisasiController::class, 'tampildatasemarbagian'])->name('tampildatasemarbagian');
    Route::get('getdatasemarbagian/{id}/{satker}', [DataRealisasiController::class, 'getdatasemarbagian'])->name('getdatasemarbagian');

    Route::get('datasaktibagian/{kdsatker}/{idbagian}',[DataRealisasiController::class,'exportdatasaktibagian'])->name('exportdatasaktibagian');
    Route::get('datasemarbagian/{kdsatker}/{idbagian}',[DataRealisasiController::class,'exportdatasemarbagian'])->name('exportdatasemarbagian');

    //terkait ikpa
    Route::get('tampilmenuikpa',[PerhitunganIkpaBulanan::class,'tampilmenurekap'])->name('tampilmenuikpa');
    Route::post('aksirekapnilaiikpa',[PerhitunganIkpaBulanan::class,'aksirekapnilaiikpa'])->name('aksirekapnilaiikpa');
    Route::post('nilaiperbagian',[PerhitunganIkpaBulanan::class,'nilaiikpabagian'])->name('nilaiikpabagian');
    Route::post('getnilaiikparealisasi',[PerhitunganIkpaBulanan::class,'nilaiikpabagian'])->name('nilaiikpabagian');
});

Route::group([
    'middleware' => ['auth','auth.role'],
    'prefix' => 'pipk',
    'role' => ['SuperAdmin','AdminPIPK'],
    'as' => 'pipk.'
], function(){
    Route::resource('akunsignifikan',AkunSignifikanController::class);
    Route::resource('jenisdokumen',AkunSignifikanController::class);
});









