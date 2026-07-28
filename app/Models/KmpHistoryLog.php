<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KmpHistoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'kmp_id',
        'user_id',
        'action',
        'description',
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
