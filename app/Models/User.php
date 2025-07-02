<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable;

    const LEVEL_SUPERADMIN = 'superadmin';
    const LEVEL_ADMIN = 'admin';
    const LEVEL_GURU = 'guru';
    const LEVEL_SISWA = 'siswa';

    const LEVELS = [
        self::LEVEL_SUPERADMIN => 'superadmin',
        self::LEVEL_ADMIN => 'admin',
        self::LEVEL_GURU => 'guru',
        self::LEVEL_SISWA => 'siswa',
    ];

    public function isAdmin() {
        return $this->level === self::LEVEL_ADMIN;
    }
    public function isSuperadmin() {
        return $this->level === self::LEVEL_SUPERADMIN;
    }
    public function isGuru() {
        return $this->level === self::LEVEL_GURU;
    }
    public function isSiswa() {
        return $this->level === self::LEVEL_SISWA;
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }
    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'level'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
}
