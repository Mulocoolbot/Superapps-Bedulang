<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kmp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kecamatan',
        'desa_kelurahan',
        'jenis',
        'nama_koperasi',
        'nomor_induk',
        'no_ahu',
        'tanggal_ahu',
        'alamat',
        'telp',
        'status_operasional',
        'status_sertifikat',
        'status_pelatihan',
        'file_sertifikat',
        'status_pembentukan',
        'jumlah_anggota',
        'jumlah_pengawas',
        'jumlah_pengurus',
        'ketua_nama',
        'ketua_foto',
        'sekretaris_nama',
        'sekretaris_foto',
        'bendahara_nama',
        'bendahara_foto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
