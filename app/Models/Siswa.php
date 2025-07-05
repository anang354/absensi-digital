<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

     protected static function booted()
    {
        // Mendaftarkan event listener saat model Siswa dihapus
        static::deleted(function (Siswa $siswa) {
            // Pastikan ada nama file foto_siswa sebelum mencoba menghapus
            if ($siswa->foto) {
                // Tentukan jalur lengkap ke file
                $filePath = $siswa->foto; // SESUAIKAN DENGAN PATH ASLI DI STORAGE ANDA

                // Cek apakah file ada di disk 'public' sebelum menghapus
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            if ($siswa->user_id && $siswa->user) {
                    $siswa->user->delete(); // Hapus record User yang berelasi
            }
        });
    }
}
