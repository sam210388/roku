<?php

namespace App\Http\Controllers\PIPK;

use App\Http\Controllers\Controller;
use App\Models\AnggaranRealisasi\Akun;
use Illuminate\Http\Request;
use App\Models\PIPK\AkunSignifikanModel;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AkunSignifikanController extends Controller
{
    public function index(Request $request){
        $judul = 'Data Akun Signifikan';
        $dataakun = Akun::all();

        if ($request->ajax()){
            $data = AkunSignifikanModel::all();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row){
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editakunsignifikan">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteakunsignifikan">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('PIPK.akunsignifikan',[
            'judul' => $judul,
            'dataakun' => $dataakun
        ]);
    }

    public function store(Request $request){
        AkunSignifikanModel::updateOrCreate([
            'id' => $request->idakunsignifikan
        ],
            [
                'kodeakun' => $request->kodeakun,
                'deskripsi' => $request->deskripsi
            ]);

        return response()->json(['success'=>'Akun Signifikan Sukses Disimpan.']);
    }

    public function edit($id){
        $akunsignifikan = AkunSignifikanModel::find($id);
        return response()->json($akunsignifikan);
    }

    public function destroy($id){
        //cek dlu penggunaanya
        $adadata = DB::table('tagihandiperiksa')->find($id,'idakunsignifikan');
        if ($adadata){
            return response()->json(['status','berhasil']);
        }else{
            AkunSignifikanModel::find($id)->delete();
            return response()->json(['status','gagal']);
        }

    }
}
