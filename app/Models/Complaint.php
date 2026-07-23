<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $appends = [
        'service_name',
    ];

    protected $fillable = [
        'user_id',
        'service_slug',
        'title',
        'description',
        'category',
        'contact_person_name',
        'contact_email',
        'contact_phone',
        'status',
        'whatsapp_phone',
        'whatsapp_sent_at',
        'whatsapp_message_id',
        'notes',
    ];

    protected $casts = [
        'whatsapp_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that submitted the complaint
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the progress history logs for this complaint
     */
    public function progressLogs(): HasMany
    {
        return $this->hasMany(ComplaintProgressLog::class, 'complaint_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get service name from slug
     */
    public function getServiceNameAttribute(): string
    {
        $services = [
            'pemerintahan' => 'PEMERINTAHAN',
            'pendidikan' => 'PENDIDIKAN',
            'kesehatan' => 'KESEHATAN',
            'perindagkop-ukm' => 'PERINDAGKOP-UKM',
            'hukum' => 'HUKUM',
            'sosial' => 'SOSIAL',
            'kependudukan' => 'KEPENDUDUKAN',
            'kepegawaian' => 'KEPEGAWAIAN',
            'pertanian' => 'PERTANIAN',
            'informasi-publik' => 'INFORMASI PUBLIK',
            'pariwisata' => 'PARIWISATA',
            'data-sektoral' => 'DATA SEKTORAL',
            'makanan-bergizi-gratis' => 'MAKANAN BERGIZI GRATIS',
        ];

        return $services[$this->service_slug] ?? ucfirst($this->service_slug);
    }
}
