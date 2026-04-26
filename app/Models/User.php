<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'level',
        'divisi',
        'is_first_login',
        'failed_attempts',
        'is_locked',
        'is_active',
        'password_changed_at', // 🆕 TAMBAHAN
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_first_login' => 'boolean',
            'is_locked' => 'boolean',
            'is_active' => 'boolean',
            'password_changed_at' => 'datetime', // 🆕 TAMBAHAN Biar otomatis jadi format tanggal/waktu
        ];
    }

    // 🆕 TAMBAHAN: Relasi untuk ngecek riwayat password user ini
    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }
}
