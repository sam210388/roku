<?php

namespace App\Models\AnggaranRealisasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringRealtime extends Model
{
    use HasFactory;

    protected $table = 'monitoringrealtime';

    public $timestamps = false;
}
