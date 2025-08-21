<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenGuru extends Model
{
    protected $guarded = ['id'];

    public function guru() : BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
    public function semester() : BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }


}
