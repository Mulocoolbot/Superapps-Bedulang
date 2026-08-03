<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_no',
        'nama',
        'jenis',
        'alamat',
        'desa',
        'kecamatan',
        'wilayah',
        'kode_pos',
    ];

    public function services()
    {
        return $this->hasMany(HealthService::class, 'health_unit_id');
    }
}
