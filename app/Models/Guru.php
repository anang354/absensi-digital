<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guru extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function absenGurus() : HasMany
    {
        return $this->hasMany(AbsenGuru::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Ketika sebuah record Guru sedang dihapus (baik melalui soft delete atau force delete)
        static::deleting(function ($guru) {
            // Periksa apakah ada user terkait dan hapus user tersebut
            if ($guru->user) {
                $guru->user->delete(); // Ini akan menghapus user terkait
            }
            if ($guru->foto) { 
                // Hapus file dari disk 'public'
                // Path yang disimpan di database adalah relatif dari root disk (misal: 'images/namafile.jpg')
                Storage::disk('public')->delete($guru->foto);
            }
        });

        // Catatan: Jika Anda menggunakan soft deletes pada model Guru,
        // event 'deleting' akan terpanggil saat soft delete.
        // Jika Anda ingin user dihapus hanya saat Guru di-force delete,
        // gunakan event 'forceDeleting' (Anda perlu menambahkan SoftDeletes trait pada Guru)
        // static::forceDeleting(function ($guru) {
        //     if ($guru->user) {
        //         $guru->user->forceDelete(); // Pastikan User juga mendukung forceDelete jika soft delete
        //     }
        // });
    }
}
