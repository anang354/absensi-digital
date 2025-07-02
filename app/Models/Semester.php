<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $guarded = ['id'];

    public function absengurus(): HasMany
    {
        return $this->hasMany(AbsenGuru::class);
    }
    public function absensiswas(): HasMany
    {
        return $this->hasMany(AbsenSiswa::class);
    }
}
