<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    protected $guarded = ['id'];
    const LAKI_LAKI = 'laki-laki';
    const PEREMPUAN = 'perempuan';
    const GENDERS = [
        self::LAKI_LAKI => 'Laki-Laki',
        self::PEREMPUAN => 'Perempuan'
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function absenSiswa() : HasMany
    {
        return $this->hasMany(AbsenSiswa::class);
    }
}
