<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Untuk membaca header baris pertama
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Untuk transaksi database

class GurusImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        // Iterasi setiap baris dari file Excel
        // $rows akan berisi data Excel dengan header sebagai kunci array
        foreach ($rows as $row) {
            // Validasi dasar (sesuaikan dengan kebutuhan Anda)
            if (empty($row['username']) || empty($row['password']) || empty($row['nama'])) {
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
                    'level'     => 'guru', // Atau sesuaikan dengan kolom 'level' di tabel users Anda
                    // Tambahkan kolom lain yang dibutuhkan tabel user Anda
                ]);

                // 2. Buat Guru baru
                Guru::create([
                    'nama'              => $row['nama'],
                    'jenis_kelamin'     => $row['jenis_kelamin'] ?? null, // Gunakan null jika kolom tidak ada
                    'nomor_handphone'   => $row['nomor_handphone'] ?? null,
                    'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir'     => isset($row['tanggal_lahir']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_lahir']) : null, // Konversi tanggal Excel
                    'alamat'            => $row['alamat'] ?? null,
                    'nik'               => $row['nik'] ?? null,
                    'nip'               => $row['nip'] ?? null,
                    'jenjang'               => $row['jenjang'] ?? null,
                    'jabatan'               => $row['jabatan'] ?? null,
                    'user_id'           => $user->id, // Tautkan dengan User yang baru dibuat
                    // Tambahkan kolom lain yang dibutuhkan tabel guru Anda
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