<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Guru;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Concerns\InteractsWithTable;

class GuruAbsenHarian extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Guru::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static string $view = 'filament.pages.guru-absen-harian';

    protected static ?string $navigationGroup = 'Absensi Guru';

    public static function canAccess(): bool
    {
        return auth()->user()->level === 'kepsek' || auth()->user()->level === 'superadmin' || auth()->user()->level === 'admin';
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        if($user->level !== 'kepsek') {
            $query = Guru::query()
                    ->whereDoesntHave('absenGurus', function (Builder $subQuery) {
                        $subQuery->whereDate('tanggal_presensi', Carbon::today());
                    })
                    ->whereHas('user');
        } else {
            $userJenjang = $user->guru->jenjang;
            $query = Guru::query()
                    ->whereDoesntHave('absenGurus', function (Builder $subQuery) {
                        $subQuery->whereDate('tanggal_presensi', Carbon::today());
                    })
                    ->whereHas('user')->where('jenjang', $userJenjang);
        }
        return $table
        ->query(
            $query
        )
        ->columns([
            TextColumn::make('nip'),
            TextColumn::make('nama')->searchable(),
            TextColumn::make('jenis_kelamin'),
            TextColumn::make('nomor_handphone'),
            TextColumn::make('jenjang'),
        ])
        ->filters([
            SelectFilter::make('jenjang')
            ->options([
                \App\Models\Guru::JENJANG_SEKOLAH
            ])
            ->visible(fn () => auth()->user()->level !== 'kepsek'),
        ])
        ->actions([
            Action::make('Hadir')
            ->color('success')
            ->action(function(Model $record){
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenGuru::create([
                    'guru_id' => $record->id,
                    'semester_id' => $semesterId,
                    'tanggal_presensi' => date('Y-m-d'),
                    'checkin' => date('H:i:s'),
                    'status' => 'hadir',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Hadir.')
                            ->success()
                            ->send();
                    return $record->nomor_handphone !== null ? $this->sendWhatsapp($record->nama, $record->nomor_handphone, 'hadir') : 'Nomor Belum Diatur';
                } else {
                    Notification::make()
                            ->title('Gagal!')
                            ->body('Absensi ' . $record->nama . ' gagal dicatat.')
                            ->danger()
                            ->send();
                }
            }),
            Action::make('Izin')
            ->color('info')
            ->form([
                // Komponen form untuk modal
                Textarea::make('keterangan')
                    ->label('Keterangan Izin')
                    ->placeholder('Masukkan alasan izin guru...')
                    ->required()
                    ->rows(5),
            ])
            ->action(function(array $data, Model $record){
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenGuru::create([
                    'guru_id' => $record->id,
                    'semester_id' => $semesterId,
                    'tanggal_presensi' => date('Y-m-d'),
                    'status' => 'izin',
                    'keterangan' => $data['keterangan'],
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Izin.')
                            ->success()
                            ->send();
                    return $record->nomor_handphone !== null ? $this->sendWhatsapp($record->nama, $record->nomor_handphone, 'izin') : 'Nomor Belum Diatur';
                } else {
                    Notification::make()
                            ->title('Gagal!')
                            ->body('Absensi ' . $record->nama . ' gagal dicatat.')
                            ->danger()
                            ->send();
                }
            }),
            Action::make('Sakit')
            ->color('primary')
            ->action(function(Model $record){
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenGuru::create([
                    'guru_id' => $record->id,
                    'semester_id' => $semesterId,
                    'tanggal_presensi' => date('Y-m-d'),
                    'status' => 'sakit',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Sakit.')
                            ->success()
                            ->send();
                    return $record->nomor_handphone !== null ? $this->sendWhatsapp($record->nama, $record->nomor_handphone, 'sakit') : 'Nomor Belum Diatur';
                } else {
                    Notification::make()
                            ->title('Gagal!')
                            ->body('Absensi ' . $record->nama . ' gagal dicatat.')
                            ->danger()
                            ->send();
                }
            }),
            Action::make('Alpha')
            ->color('danger')
            ->action(function(Model $record){
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenGuru::create([
                    'guru_id' => $record->id,
                    'semester_id' => $semesterId,
                    'tanggal_presensi' => date('Y-m-d'),
                    'status' => 'alpha',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Alpha.')
                            ->success()
                            ->send();
                    return $record->nomor_handphone !== null ? $this->sendWhatsapp($record->nama, $record->nomor_handphone, 'alpha') : 'Nomor Belum Diatur';
                } else {
                    Notification::make()
                            ->title('Gagal!')
                            ->body('Absensi ' . $record->nama . ' gagal dicatat.')
                            ->danger()
                            ->send();
                }
            }),
        ], position: ActionsPosition::BeforeColumns);
    }

    public function sendWhatsapp($namaGuru, $phoneNumber, $status)
    {
        $token = \App\Models\Pengaturan::first()->value('token_whatsapp');
        $user = \App\Models\User::whereIn('level', ['superadmin', 'admin'])->get();

        if($token === null) { return; }

        if($status === 'hadir') {
            $fonnteApiUrl = 'https://api.fonnte.com/send'; // URL ini bisa berubah, cek dokumentasi FonNte terbaru
            $message = "CHECKIN !\n";
            $message .= "\n"; // Baris kosong untuk spasi
            $message .= "Nama  : " . $namaGuru . ".\n"; // Baris baru setelah nama siswa
            $message .= "Telah melakukan presensi hari ini:\n"; // Baris baru
            $message .= "- Tanggal: " . date('l, d M Y') . "\n"; // Baris baru
            $message .= "- Waktu: " .  date('H:i:s') . "\n"; // Baris baru
            $message .= "\n"; // Baris kosong untuk spasi
            $message .= "\n *_Pesan otomatis tidak perlu dibalas._";
        } else {
            $fonnteApiUrl = 'https://api.fonnte.com/send'; // URL ini bisa berubah, cek dokumentasi FonNte terbaru
            $message = strtoupper($status)."!\n";
            $message .= "\n"; // Baris kosong untuk spasi
            $message .= "Nama  : " . $namaGuru . ".\n"; // Baris baru setelah nama siswa
            $message .= "- Tanggal: " . date('l, d M Y') . "\n"; // Baris baru
            $message .= "\n"; // Baris kosong untuk spasi
            $message .= "\n *_Pesan otomatis tidak perlu dibalas._";
        }
        
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
