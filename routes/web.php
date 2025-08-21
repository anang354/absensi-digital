<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuruScanController;
use App\Http\Controllers\SiswaScanController;
 use Barryvdh\DomPDF\Facade\Pdf;
 use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import Facade QrCode
 
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    //After Login the routes are accept by the loginUsers...
    Route::post('/guru-scan/store', [GuruScanController::class, 'store']);
    Route::post('/siswa-scan/store', [SiswaScanController::class, 'store']);
    Route::get('/siswa-scan', [SiswaScanController::class, 'index']);
    Route::get('/pengaturan', function() {
        $pengaturan = \App\Models\Pengaturan::select('latitude', 'longitude', 'radius')->first();
        $posisi = [$pengaturan->latitude, $pengaturan->longitude, $pengaturan->radius];
        return $posisi;
    });
});

Route::get('/whatsapp', function() {
    $fonnteToken = 'sG3KNqMcVcYx62DMfaos'; // Ambil dari .env atau ganti
        $targetNumber = '6281334659292'; // Ganti dengan nomor tujuan (diawali kode negara, tanpa '+')
        $message = 'Halo! Ini adalah pesan WhatsApp dari aplikasi Laravel Anda menggunakan cURL.';

        // Validasi nomor telepon
        if (empty($targetNumber) || !preg_match('/^\d+$/', $targetNumber)) {
            return response()->json(['status' => 'error', 'message' => 'Nomor tujuan tidak valid.'], 400);
        }

        // URL API FonNte untuk pengiriman pesan teks
        $fonnteApiUrl = 'https://api.fonnte.com/send'; // URL ini bisa berubah, cek dokumentasi FonNte terbaru

        try {
            // Inisialisasi sesi cURL
            $ch = curl_init();

            // Set URL dan opsi cURL
            curl_setopt($ch, CURLOPT_URL, $fonnteApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan transfer sebagai string daripada output langsung
            curl_setopt($ch, CURLOPT_POST, true); // Mengatur metode request ke POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ // Membangun query string dari array data
                'target' => $targetNumber,
                'message' => $message,
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Menambahkan header Authorization
                'Authorization: ' . $fonnteToken,
            ]);

            // Opsional: Untuk debugging, hapus di produksi
            // curl_setopt($ch, CURLOPT_VERBOSE, true); // Menampilkan detail proses cURL
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // HANYA UNTUK DEV! JANGAN DI PRODUKSI TANPA SERTIFIKAT CA YANG BENAR

            // Eksekusi sesi cURL dan dapatkan respons
            $response = curl_exec($ch);

            // Periksa jika ada error cURL
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                Log::error("cURL Error: " . $error_msg);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kesalahan cURL: ' . $error_msg
                ], 500);
            }

            // Dapatkan informasi HTTP status code
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Tutup sesi cURL
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($http_code >= 200 && $http_code < 300) {
                // Berhasil mengirim pesan (status code 2xx)
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pesan WhatsApp berhasil dikirim.',
                    'fonnte_response' => $responseData
                ]);
            } else {
                // Gagal mengirim pesan
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim pesan WhatsApp. Status HTTP: ' . $http_code,
                    'fonnte_response' => $responseData
                ], $http_code);
            }

        } catch (\Exception $e) {
            // Tangani error jika terjadi masalah koneksi atau lainnya
            Log::error("Exception in WhatsappController: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal: ' . $e->getMessage()
            ], 500);
        }
});

