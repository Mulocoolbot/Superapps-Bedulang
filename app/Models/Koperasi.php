<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Koperasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_koperasi',
        'tanggal_badan_hukum',
        'kecamatan',
        'desa_kelurahan',
        'tahun',
        'import_log_id',
        'created_by',
    ];

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(KoperasiImportLog::class, 'import_log_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
