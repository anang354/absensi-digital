<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $guarded = ['id'];

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    const JENJANG_SMP = 'smp';
    const JENJANG_SMK = 'smk';
    const JENJANG_SEKOLAH = [
        self::JENJANG_SMP => 'SMP',
        self::JENJANG_SMK => 'SMK',
    ];
}
