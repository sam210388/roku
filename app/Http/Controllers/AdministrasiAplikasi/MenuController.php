<?php

namespace App\Http\Controllers\AdministrasiAplikasi;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;



class MenuController extends Controller
{
    public function tampilmenu(){
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            if (Auth::check()){
                $role = Auth::user()->role_id;
                if ($role == 1){
                    // Add some items to the menu...
                    $event->menu->add([
                        'text'    => 'Administrasi Aplikasi',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Kelola BearerKey',
                                'route'  => 'admin.tampil-bearerkey',
                            ],
                            [
                                'text' => 'Kelola User',
                                'route'  => 'admin.tampil-user',
                            ],
                            [
                                'text' => 'Kelola Kewenangan',
                                'route'  => 'admin.tampil-roles',
                            ],
                        ],
                    ]);
                    $event->menu->add([
                        'text'    => 'Referensi Unit',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Deputi',
                                'route'  => 'anggaran.tampil-deputi',
                            ],
                            [
                                'text' => 'Biro',
                                'route'  => 'anggaran.tampil-biro',
                            ],
                            [
                                'text' => 'Bagian',
                                'route'  => 'anggaran.tampil-bagian',
                            ],

                        ],
                    ]);
                    $event->menu->add([
                        'text'    => 'Referensi Anggaran',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Program',
                                'route'  => 'anggaran.tampil-program',
                            ],
                            [
                                'text' => 'Kegiatan',
                                'route'  => 'anggaran.tampil-kegiatan',
                            ],
                            [
                                'text' => 'Output',
                                'route'  => 'anggaran.tampil-output',
                            ],
                            [
                                'text' => 'SubOutput',
                                'route'  => 'anggaran.tampil-suboutput',
                            ],
                            [
                                'text' => 'Komponen',
                                'route'  => 'anggaran.tampil-komponen',
                            ],
                        ],
                    ]);

                    $event->menu->add([
                        'text'    => 'Data Anggaran dan Realisasi',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Data Refstatus',
                                'route'  => 'anggaran.tampil-refstatus',
                            ],
                            [
                                'text' => 'Anggaran Bagian',
                                'route'  => 'anggaran.tampil-anggaranbagian',
                            ],
                            [
                                'text' => 'Daftar SP2D',
                                'route'  => 'anggaran.tampil-tampilrealisasi',
                            ],
                            [
                                'text' => 'Export Realisasi',
                                'route'  => 'anggaran.monitoringrealisasi',
                            ],
                        ],
                    ]);

                    $event->menu->add([
                        'text'    => 'Modul IKPA',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Realisasi Biro',
                                'route'  => 'ikpa.tampilrealisasibiro',
                            ],
                            [
                                'text' => 'Penilaian IKPA',
                                'route'  => 'ikpa.tampilmenuikpa',
                            ],
                        ],
                    ]);

                    $event->menu->add([
                        'text'    => 'Modul Anggota',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Kelola Nomor Anggota',
                                'route'  => 'Anggota.nomoranggota',
                            ],
                            [
                                'text' => 'Kelola Fraksi',
                                'route'  => 'Anggota.fraksi',
                            ],
                            [
                                'text' => 'Kelola Nama Anggota',
                                'route'  => 'Anggota.namaanggota',
                            ],
                        ],
                    ]);
                    $event->menu->add([
                        'text'    => 'Administrasi PIPK',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Akun Signifikan',
                                'url'  => 'pipk/akunsignifikan',
                            ],
                            [
                                'text' => 'Jenis Dokumen Tagihan',
                                'url'  => 'pipk/jenisdokumen',
                            ],
                        ],
                    ]);
                }else if ($role == 2){
                    $event->menu->add([
                        'text'    => 'Referensi Unit',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Deputi',
                                'route'  => 'anggaran.tampil-deputi',
                            ],
                            [
                                'text' => 'Biro',
                                'route'  => 'anggaran.tampil-biro',
                            ],
                            [
                                'text' => 'Bagian',
                                'route'  => 'anggaran.tampil-bagian',
                            ],

                        ],
                    ]);
                    $event->menu->add([
                        'text'    => 'Referensi Anggaran',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Program',
                                'route'  => 'anggaran.tampil-program',
                            ],
                            [
                                'text' => 'Kegiatan',
                                'route'  => 'anggaran.tampil-kegiatan',
                            ],
                            [
                                'text' => 'Output',
                                'route'  => 'anggaran.tampil-output',
                            ],
                            [
                                'text' => 'SubOutput',
                                'route'  => 'anggaran.tampil-suboutput',
                            ],
                            [
                                'text' => 'Komponen',
                                'route'  => 'anggaran.tampil-komponen',
                            ],
                        ],
                    ]);

                    $event->menu->add([
                        'text'    => 'Data Anggaran dan Realisasi',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Data Refstatus',
                                'route'  => 'anggaran.tampil-refstatus',
                            ],
                            [
                                'text' => 'Anggaran Bagian',
                                'route'  => 'anggaran.tampil-anggaranbagian',
                            ],
                            [
                                'text' => 'Daftar SP2D',
                                'route'  => 'anggaran.tampil-tampilrealisasi',
                            ],
                            [
                                'text' => 'Export Realisasi',
                                'route'  => 'anggaran.monitoringrealisasi',
                            ],
                        ],
                    ]);
                }else if ($role == 6){
                    $event->menu->add([
                        'text'    => 'Administrasi PIPK',
                        'icon'    => 'fas fa-fw fa-list',
                        'submenu' => [
                            [
                                'text' => 'Akun Signifikan',
                                'route'  => 'pipk.akunsignifikan',
                            ],
                        ],
                    ]);
                }
            }
        });
    }

}
