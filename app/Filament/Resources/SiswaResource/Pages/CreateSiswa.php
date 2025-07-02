<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Filament\Resources\SiswaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiswa extends CreateRecord
{
    protected static string $resource = SiswaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Ambil data username dan password dari form
        $username = $data['username'];
        $password = $data['password'];
        $nama = $data['nama'];
        
        // 2. Hapus username dan password dari data guru, karena ini untuk tabel User
        unset($data['username']);
        unset($data['password']);

        // 3. Buat user baru
        $user = User::create([
            'name' => $nama,
            'email' => $username.'@example.com',
            'username' => $username,
            'password' => $password,
            'level' => 'siswa',
        ]);
        //dd($user->id);
        // 4. Tambahkan user_id ke data guru sebelum disimpan
        $data['user_id'] = $user->id;
        
        return $data;
    }
    
    // --- OPSI TAMBAHAN UNTUK EDIT (Sangat Direkomendasikan) ---
    // Gunakan ini jika Anda ingin memastikan username dari user terkait muncul saat mengedit Guru
    public static function mutateFormDataBeforeFill(array $data, Siswa $record): array
    {
        // Jika Siswa memiliki user terkait, tambahkan username ke data form
        if ($record->user) {
            $data['username'] = $record->user->username;
        }
        return $data;
    }
}
