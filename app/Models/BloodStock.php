<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_type',
        'amount',
        'unit',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
