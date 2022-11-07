<?php

namespace App\Models\ReferensiUnit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Biro extends Model
{
    use HasFactory;
    protected $table = 'biro';

    public function Deputi(){
        return $this->belongsTo(Deputi::Class);
    }

    public function bagian(){
        return $this->hasMany(Bagian::class);
    }

}
