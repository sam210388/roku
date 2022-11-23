<?php

namespace App\Models\PIPK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkunSignifikanModel extends Model
{
    use HasFactory;

    protected $table = 'akunsignifikan';

    public $timestamp = false;

    protected $fillable = ['tahunanggaran','kodeakun','deskripsi'];
}
