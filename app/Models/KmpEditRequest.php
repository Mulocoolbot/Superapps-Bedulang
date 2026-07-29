<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KmpEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'kmp_id',
        'user_id',
        'requested_data',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_data' => 'array',
    ];

    public function kmp()
    {
        return $this->belongsTo(Kmp::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
