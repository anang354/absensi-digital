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
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        $guruId = auth()->user()->guru->id;
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
                //$simpan = DB::table('absen_gurus')->insert($data);
            } catch(Exception $e) {
                echo "error|Maaf gagal absen, hubungi tim IT|in";
            }
        } else {
            try {
            $getAbsen = AbsenGuru::where('guru_id', $guruId)->where('tanggal_presensi', $tanggal_presensi)->first();
            $getAbsen->checkout = $jam;
            $getAbsen->lokasi_out = $lokasi;
            $getAbsen->foto_out = 'uploads/absensi/'.$fileName;
            $getAbsen->save();
            Storage::put($file, $image_base64);
            echo "success|Terimakasih, Hati-hati dijalan :)|out";
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
}
