<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\AbsenSiswa;
use Illuminate\Http\Request;
use App\Jobs\SendWhatsappWali;
use Illuminate\Support\Facades\DB;

class SiswaScanController extends Controller
{
    public function index()
    {
        
        $history = AbsenSiswa::with('siswa')
                         ->limit(10) // Ambil 10 data terbaru, sesuaikan
                         ->where('tanggal', date('Y-m-d'))
                         ->where('user_pengabsen', auth()->user()->username)
                         ->orderBy('waktu', 'desc')
                         ->get()
                         ->map(function ($record) {
                             return [
                                 'nama' => $record->siswa->nama ?? 'N/A',
                                 'tanggal' => $record->tanggal, // Format tanggal
                                 'waktu' => $record->waktu, // Format tanggal
                                 'tipe' => ucfirst($record->tipe_absen), // Kapitalisasi awal
                             ];
                         });

        return response()->json($history);
    }
    //
    public function store(Request $request) 
    {
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        $siswa  = Siswa::where('nisn', $request->nisn)->first();
        if(!$siswa) {
            echo "error|Siswa tidak ditemukan!";
            return;
        }
        $check = AbsenSiswa::where('siswa_id', $siswa->id)->where('tanggal', date('Y-m-d'))->where('tipe_absen', $request->tipe)->count();
        if($check > 0) {
            echo "error|Siswa ini sudah absen sholat ".$request->tipe;
        } else {
            $img = $siswa->foto ? asset('storage/'.$siswa->foto) : asset('storage/images/user-default.png');
            //$img = asset('storage/'.$siswa->foto);
            try {
                $absen = AbsenSiswa::create([
                    "siswa_id" => $siswa->id,
                    "semester_id" => $semesterId,
                    "user_pengabsen" => auth()->user()->username,
                    "tipe_absen" => $request->tipe,
                    "tanggal" => date('Y-m-d'),
                    "waktu" => date('H:i:s'),
                    "status" => "hadir",
                ]);
                if($absen) {
                    echo "success|Berhasil absen|".$img."|".$siswa->nama."|".$siswa->kelas->nama_kelas;
                    if($siswa->nomor_hp !== null && $request->tipe !== 'dzuhur') {
                        $phoneNumber = $siswa->nomor_hp;
                        $message = "Assalamualaikum Wr.Wb\n";
                        $message .= "Bapak/Ibu wali murid dari " . $siswa->nama . ".\n"; // Baris baru setelah nama siswa
                        $message .= "Putra/putri Anda telah melakukan presensi hari ini:\n"; // Baris baru
                        $message .= "- Tanggal: " . date('l, d M Y') . "\n"; // Baris baru
                        $message .= "- Waktu: " .  date('H:i:s') . "\n"; // Baris baru
                        $message .= "Sholat: " . $request->tipe . "\n"; // Baris baru
                        $message .= "\n"; // Baris kosong untuk spasi
                        $message .= "Terima kasih atas perhatiannya.\n";
                        $message .= "\n *Pesan otomatis tidak perlu dibalas.";

                        //MenggunakanJobCount
                        //  $jobCount = DB::table('jobs')
                        // ->where('queue', 'default')
                        // ->where('payload', 'like', '%SendWhatsappWali%')
                        // ->count();
                        // $delay = now()->addSeconds(90 * $jobCount);

                        //Menggunakan caching / cache
                        // $lastDelay = cache()->get('last_wa_delay', now());
                        // $nextDelay = \Carbon\Carbon::parse($lastDelay)->addSeconds(60);
                        // cache()->put('last_wa_delay', $nextDelay, now()->addMinutes(10));

                        $counter = cache()->get('fonnte_scan_counter', 0);

                        // Hitung delay berdasarkan counter
                        $delaySeconds = 30 * $counter;
                        $nextCounter = $counter + 1;
                        // Simpan kembali ke cache, expired otomatis jika tidak scan dalam 5 menit
                        cache()->put('fonnte_scan_counter', $nextCounter, now()->addMinutes(10));
                        // echo 'delay '.$delay;
                        SendWhatsappWali::dispatch($phoneNumber, $message, $delaySeconds); 
                    }
                } else {
                    echo "Gagal";
                }
            } catch (Exception $e) {
                echo "error|Gagal mengiput kedalam database! Silahkan hubungi IT";
                return;
            }
        }
        
    }
}
