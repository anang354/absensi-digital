<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Untuk membaca header baris pertama
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Untuk transaksi database

class SiswaImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        // Iterasi setiap baris dari file Excel
        // $rows akan berisi data Excel dengan header sebagai kunci array
        foreach ($rows as $row) {
            // Validasi dasar (sesuaikan dengan kebutuhan Anda)
            if (empty($row['kelas_id']) || empty($row['username']) || empty($row['password']) || empty($row['nama'])) {
                // Lewati baris ini atau log error jika data tidak lengkap
                continue;
            }

            // Mulai transaksi database untuk memastikan konsistensi
            DB::transaction(function () use ($row) {
                // 1. Buat User baru
                $user = User::create([
                    'name'      => $row['nama'], // Ambil dari kolom 'nama' di Excel
                    'username'  => $row['username'],
                    'password'  => Hash::make($row['password']),
                    'email'     => $row['username'] . '@example.com', // Contoh default email
                    'level'     => 'siswa', // Atau sesuaikan dengan kolom 'level' di tabel users Anda
                    // Tambahkan kolom lain yang dibutuhkan tabel user Anda
                ]);

                // 2. Buat Guru baru
                Siswa::create([
                    'kelas_id'        => $row['kelas_id'], // Tautkan dengan User yang baru dibuat
                    'user_id'         => $user->id, // Tautkan dengan User yang baru dibuat
                    'nama'            => $row['nama'],
                    'jenis_kelamin'   => $row['jenis_kelamin'] ?? null, // Gunakan null jika kolom tidak ada
                    'nisn'            => $row['nisn'] ?? null,
                    'nomor_hp'        => $row['nomor_hp'] ?? null,
                    'alamat'          => $row['alamat'] ?? null,
                    'wali'            => $row['wali'] ?? null,
                ]);
            });
        }
    }

    /**
     * Tentukan apakah baris pertama adalah header
     * @return int
     */
    public function headingRow(): int
    {
        return 1; // Baris pertama adalah header
    }
}
