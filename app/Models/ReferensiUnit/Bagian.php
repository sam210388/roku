<?php

namespace App\Models\ReferensiUnit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    use HasFactory;

    protected $table = 'bagian';

    public function deputi(){
        return $this->belongsTo(Deputi::class);

    }

    public function biro(){
        return $this->belongsTo(Biro::class);
    }
}
