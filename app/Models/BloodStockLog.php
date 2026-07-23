<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodStockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_type',
        'old_amount',
        'new_amount',
        'delta',
        'updated_by',
    ];

    /**
     * Get the user who updated the blood stock.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
