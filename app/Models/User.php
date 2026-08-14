<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'service_slug',
        'kdmp_role',
        'kdmp_kecamatan',
        'kdmp_admin_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isPublik(): bool
    {
        return $this->role === 'publik';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function canAccessService(?string $serviceSlug): bool
    {
        if ($this->isSuperAdmin() || $this->isPimpinan()) {
            return true;
        }

        if ($this->isAdminKependudukan()) {
            return $serviceSlug === 'kependudukan' || empty($serviceSlug);
        }

        if (empty($this->service_slug)) {
            return true;
        }

        if ($serviceSlug === 'pengaduan-bedulang') {
            return $this->isAdmin() || $this->isPimpinan();
        }

        return $this->service_slug === $serviceSlug;
    }

    // KDMP Role Helpers
    public function isKdmpAdmin(): bool
    {
        return $this->kdmp_role === 'admin_kdmp' || $this->isSuperAdmin();
    }

    public function isKdmpKecamatanAdmin(): bool
    {
        return $this->kdmp_role === 'admin_kecamatan';
    }

    public function isKdmpKmpAdmin(): bool
    {
        return $this->kdmp_role === 'admin_kmp';
    }

    public function isAdminKoperasi(): bool
    {
        return $this->role === 'admin_koperasi' || ($this->role === 'admin' && $this->service_slug === 'koperasi');
    }

    public function isAdminKependudukan(): bool
    {
        return $this->role === 'admin_kependudukan' || ($this->role === 'admin' && $this->service_slug === 'kependudukan');
    }

    public function kmp()
    {
        return $this->hasOne(Kmp::class);
    }
}
