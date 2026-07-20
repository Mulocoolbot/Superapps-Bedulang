<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'lingkungan' => 'LINGKUNGAN',
            'geografis' => 'GEOGRAFIS',
            'pariwisata' => 'PARIWISATA',
            'data-sektoral' => 'DATA SEKTORAL',
        ];

        return $services[$this->service_slug] ?? ucfirst($this->service_slug);
    }
}
