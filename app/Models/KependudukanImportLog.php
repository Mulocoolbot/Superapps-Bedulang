<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KependudukanImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_key',
        'category_name',
        'tahun',
        'semester',
        'file_name',
        'total_rows',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function records()
    {
        return $this->hasMany(KependudukanRecord::class, 'import_log_id');
    }
}
