<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenSiswa extends Model
{
    protected $guarded = ['id'];

    const ABSEN_DHUHA = 'dhuha';
    const ABSEN_ASHAR = 'ashar';

    const TIPE_ABSEN = [
        self::ABSEN_DHUHA => 'Dhuha',
        self::ABSEN_ASHAR => 'Ashar',
    ];

    public function siswa() : BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
    public function semester() : BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
