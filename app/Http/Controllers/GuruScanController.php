<?php

namespace App\Http\Controllers;

use App\Models\AbsenGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuruScanController extends Controller
{

    public function store(Request $request)
    {
        $pengaturan = \App\Models\Pengaturan::select('latitude', 'longitude', 'radius')->first();
        //$lokasiKantor = [$pengaturan->latitude, $pengaturan->longitude];
        //1.166753, 104.018848
        // "1.1667241","104.018855"
        $batasPulang = '11:30:00';
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        $guruId = auth()->user()->guru->id;
        $guruNama = auth()->user()->guru->nama;
        $phoneNumber = auth()->user()->guru->nomor_handphone;
        $tanggal_presensi = date('Y-m-d');
        $cekPresensi = AbsenGuru::where('guru_id', $guruId)->where('tanggal_presensi', $tanggal_presensi)->count();      
        $lokasi = $request->lokasi;
        $lokasiUser = explode(",",$lokasi);
        $jarak = $this->distance($pengaturan->latitude, $pengaturan->longitude, $lokasiUser[0], $lokasiUser[1]);
        $radius = round($jarak['meters']);
        $image = $request->image;
        $isStatus = $cekPresensi == 0 ? 'checkin' : 'checkout';
        $folderPath = "public/uploads/absensi/";
        $jam  = date('H:i:s');
        $formatName = $isStatus.'-'.Auth()->user()->name.'-'.$tanggal_presensi;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName.".png";
        $file = $folderPath . $fileName;
        if($radius > $pengaturan->radius) {
            echo "error|Maaf, Anda Berada ".$radius." meter Diluar Radius Absen!";
        } else {
                if($cekPresensi == 0) {
            $data = [
                'guru_id' => $guruId,
                'tanggal_presensi' => $tanggal_presensi,
                'checkin' => $jam,
                'lokasi_in' => $lokasi,
                'foto_in' => $fileName,
                'semester_id' => $semesterId
            ];
            try {
                $absenGuru = new AbsenGuru;
                $absenGuru->guru_id = $guruId;
                $absenGuru->tanggal_presensi = $tanggal_presensi;
                $absenGuru->checkin = $jam;
                $absenGuru->lokasi_in = $lokasi;
                $absenGuru->foto_in = 'uploads/absensi/'.$fileName;
                $absenGuru->semester_id = $semesterId;
                $absenGuru->status = 'hadir';
                $absenGuru->save();
                Storage::put($file, $image_base64);
                echo "success|Selamat bekerja :)|in";
                return $phoneNumber !== null  ? $this->sendWhatsapp($guruNama, $isStatus, $phoneNumber) : 'Nomor Hp belum diatur';
                //$simpan = DB::table('absen_gurus')->insert($data);
            } catch(Exception $e) {
                echo "error|Maaf gagal absen, hubungi tim IT|in";
            }
        } else {
            if($jam < $batasPulang) {
                echo "error|Error, Belum waktunya pulang!|out";
                return;
            }
            try {
                $getAbsen = AbsenGuru::where('guru_id', $guruId)->where('tanggal_presensi', $tanggal_presensi)->first();
                $getAbsen->checkout = $jam;
                $getAbsen->lokasi_out = $lokasi;
                $getAbsen->foto_out = 'uploads/absensi/'.$fileName;
                $getAbsen->save();
                Storage::put($file, $image_base64);
                echo "success|Terimakasih, Hati-hati dijalan :)|out";
                return $phoneNumber !== null  ? $this->sendWhatsapp($guruNama,  $isStatus, $phoneNumber) : 'Nomor Hp belum diatur';
            } catch(Exception $e) {
                echo "error|Maaf gagal absen, hubungi tim IT|out";
            }
        }
        }
        
        
    }

     //Menghitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    function sendWhatsapp($namaGuru, $status, $phoneNumber)
    {
        $token = \App\Models\Pengaturan::first()->value('token_whatsapp');
        $user = \App\Models\User::whereIn('level', ['superadmin', 'admin'])->get();

        if($token === null) { return; }

        $fonnteApiUrl = 'https://api.fonnte.com/send'; // URL ini bisa berubah, cek dokumentasi FonNte terbaru
        
        $message = strtoupper($status)."!\n";
        $message .= "\n"; // Baris kosong untuk spasi
        $message .= "Nama  : " . $namaGuru . ".\n"; // Baris baru setelah nama siswa
        $message .= "Telah melakukan presensi hari ini:\n"; // Baris baru
        $message .= "- Tanggal: " . date('l, d M Y') . "\n"; // Baris baru
        $message .= "- Waktu: " .  date('H:i:s') . "\n"; // Baris baru
        $message .= "\n"; // Baris kosong untuk spasi
        $message .= "\n *_Pesan otomatis tidak perlu dibalas._";
        
        try {
            // Inisialisasi sesi cURL
            $ch = curl_init();

            // Set URL dan opsi cURL
            curl_setopt($ch, CURLOPT_URL, $fonnteApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan transfer sebagai string daripada output langsung
            curl_setopt($ch, CURLOPT_POST, true); // Mengatur metode request ke POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ // Membangun query string dari array data
                'target' => $phoneNumber,
                'message' => $message,
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
               return;
                
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
