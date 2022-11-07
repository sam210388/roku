<?php

namespace App\Models\AdministrasiAplikasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelolaAkses extends Model
{
    use HasFactory;

    protected $table = 'permissions';
}
