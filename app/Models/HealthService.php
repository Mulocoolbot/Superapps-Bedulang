<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthService extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_layanan',
        'health_unit_id',
        'kelompok_utama',
        'subkelompok',
        'no_item',
        'jenis_layanan',
    ];

    public function healthUnit()
    {
        return $this->belongsTo(HealthUnit::class, 'health_unit_id');
    }
}
