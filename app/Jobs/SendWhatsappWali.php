<?php

namespace App\Jobs;

use Log;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsappWali implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phoneNumber;
    public $message;
    public $delaySeconds;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phoneNumber, string $message, int $delaySeconds)
    {
        //
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->delaySeconds = $delaySeconds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $user = \App\Models\User::whereIn('level', ['superadmin', 'admin'])->get();
        // Jika ada delay spesifik, tambahkan di sini
        // if ($this->delaySeconds > 0) {
        //     sleep($this->delaySeconds); // Menunda eksekusi Job ini
        // }
        $token = \App\Models\Pengaturan::first()->value('token_whatsapp');
        if($token === null) { return; }

        $fonnteApiUrl = 'https://api.fonnte.com/send'; // URL ini bisa berubah, cek dokumentasi FonNte terbaru

        try {
            // Inisialisasi sesi cURL
            $ch = curl_init();

            // Set URL dan opsi cURL
            curl_setopt($ch, CURLOPT_URL, $fonnteApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan transfer sebagai string daripada output langsung
            curl_setopt($ch, CURLOPT_POST, true); // Mengatur metode request ke POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ // Membangun query string dari array data
                'target' => $this->phoneNumber,
                'message' => $this->message,
                'delay' => $this->delaySeconds,
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Menambahkan header Authorization
                'Authorization: ' . $token,
            ]);

            // Eksekusi sesi cURL dan dapatkan respons
            $response = curl_exec($ch);

            // Periksa jika ada error cURL
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                Notification::make()
                    ->title('Kesalahan Pengiriman Whatsapp')
                    ->danger()
                    ->body($error_msg)
                    ->sendToDatabase($user);
            }

            // Dapatkan informasi HTTP status code
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Tutup sesi cURL
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($http_code >= 200 && $http_code < 300) {
                // Berhasil mengirim pesan (status code 2xx)
                Log::info('Mengirim WA ke: ' . $this->phoneNumber . ' pada ' . now());
                
            } else {
                // Gagal mengirim pesan
                Notification::make()
                    ->title('Kesalahan Pengiriman Whatsapp')
                    ->danger()
                    ->body($responseData)
                    ->sendToDatabase($user);
            }

        } catch (\Exception $e) {
            // Tangani error jika terjadi masalah koneksi atau lainnya
            Notification::make()
                    ->title('Kesalahan Pengiriman Whatsapp')
                    ->danger()
                    ->body($e->getMessage())
                    ->sendToDatabase($user);
        }
    }
}
