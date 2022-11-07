<?php

namespace App\Models\ReferensiUnit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deputi extends Model
{
    use HasFactory;
    protected $table = 'deputi';

    public function biro(){
        return $this->hasMany(Biro::class);
    }

    public function bagian()
    {
        return $this->hasMany(Bagian::class);
    }
}
