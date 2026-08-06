<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KependudukanRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_log_id',
        'tahun',
        'semester',
        'category_key',
        'kecamatan',
        'desa_kelurahan',
        'payload',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function importLog()
    {
        return $this->belongsTo(KependudukanImportLog::class, 'import_log_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
