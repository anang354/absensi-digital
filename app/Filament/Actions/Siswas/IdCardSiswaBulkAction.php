<?php

namespace App\Filament\Actions\Siswas;

use Livewire\Component;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
 use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import Facade QrCode

class IdCardSiswaBulkAction {
    public static function make(): BulkAction
    {
        return BulkAction::make('print_id_cards')
                        ->label('Cetak ID Card')
                        ->icon('heroicon-o-printer')
                        ->action(function (Collection $records) {
                            $setting = \App\Models\Pengaturan::select('nama_sekolah', 'logo_sekolah')->first();
                            if(!$setting) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal mencetak ID Card')
                                    ->body('Silahkan lengkapi pengaturan terlebih dahulu')
                                    ->danger()
                                    ->send();
                                return; // Hentikan eksekusi action
                            }
                            $path = public_path().'/storage/'.$setting->logo_sekolah;
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $logo = 'data:image/'.$type.';base64,'.base64_encode($data);

                            $dataSiswa = $records->map(function ($record) {
                                                        
                                            return [
                                                'nama' => $record->nama,
                                                'nisn' => $record->nisn,
                                                'kelas' => $record->kelas->nama_kelas,
                                                'foto' => $record->foto,
                                            ];
                                        })->toArray();

                            foreach ($dataSiswa as &$siswa) { // Gunakan referensi (&) agar perubahan tersimpan
                                $nisn = (string) $siswa['nisn']; // Pastikan NISN adalah string

                                $qrCodeBinary = QrCode::format('png') // Format gambar (png, svg, jpg)
                                                    ->size(100)       // Ukuran QR code dalam piksel
                                                    ->errorCorrection('H') // Level koreksi error (L, M, Q, H)
                                                    ->generate($nisn);

                                // Encode gambar QR code ke Base64
                                $qrCodeBase64 = 'data:image/png;base64,'.base64_encode($qrCodeBinary);

                                // Simpan QR code Base64 ke data siswa
                                $siswa['qr_code'] = $qrCodeBase64;
                            }
                            $pdf = Pdf::loadView('templates.idcardsiswa', [
                                'data' => $dataSiswa,
                                'logo' => $logo,
                                'namaSekolah' => $setting->nama_sekolah
                            ]);
                            $tanggal = date('Y-m-d');
                             return response()->stream(function() use ($pdf, $tanggal) {
                                    echo $pdf->stream('id_cards_' . $tanggal . '.pdf');
                                }, 200, [
                                    'Content-Type' => 'application/pdf',
                                    'Content-Disposition' => 'inline; filename="id_cards_' . $tanggal . '.pdf"',
                                ]);
                        });
    }

}