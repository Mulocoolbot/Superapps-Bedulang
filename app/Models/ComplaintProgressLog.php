<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintProgressLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'user_id',
        'status',
        'notes',
    ];

    /**
     * Get the complaint that owns the progress log.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * Get the user (admin) who performed this status update.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
