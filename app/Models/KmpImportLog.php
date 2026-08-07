<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KmpImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_name',
        'total_rows',
        'status',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kmps(): HasMany
    {
        return $this->hasMany(Kmp::class, 'import_log_id');
    }
}
