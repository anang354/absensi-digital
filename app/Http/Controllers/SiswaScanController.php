<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\AbsenSiswa;
use Illuminate\Http\Request;

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
