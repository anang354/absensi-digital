<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Siswa;
use Filament\Pages\Page;
use App\Models\AbsenSiswa;
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

class AbsenAshar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Absensi Siswa';

    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.absen-ashar';

    public static function canAccess(): bool
    {
        return auth()->user()->level === 'kepsek' || auth()->user()->level === 'guru' || auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin';
    }

    public function table(Table $table): Table
    {
        return $table
        ->query(
            Siswa::query()
                    ->whereDoesntHave('absenSiswa', function (Builder $subQuery) {
                        $subQuery->whereDate('tanggal', Carbon::today());
                        $subQuery->where('tipe_absen', AbsenSiswa::ABSEN_ASHAR);
                    })
                    ->whereHas('user')
        )
        ->columns([
            TextColumn::make('nisn'),
            TextColumn::make('nama')->searchable(),
            TextColumn::make('kelas.nama_kelas'),
            TextColumn::make('jenis_kelamin')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nomor_hp')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('wali'),
        ])
        ->filters([
            SelectFilter::make('kelas')
                    ->relationship('kelas', 'nama_kelas') // Ini adalah kuncinya!
                    ->label('Filter Berdasarkan Kelas')
                    ->placeholder('Pilih Kelas')
                    ->options(
                        \App\Models\Kelas::pluck('nama_kelas', 'id')->toArray()
                    ),
        ])
        ->actions([
            Action::make('Hadir')
            ->color('success')
            ->action(function(Model $record){
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenSiswa::create([
                    'siswa_id' => $record->id,
                    'semester_id' => $semesterId,
                    'user_pengabsen' => auth()->user()->username,
                    'tipe_absen' => 'ashar',
                    'tanggal' => date('Y-m-d'),
                    'waktu' => date('H:i:s'),
                    'status' => 'hadir',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Hadir.')
                            ->success()
                            ->send();
                    $this->sendWhatsappWali($record->nama, $record->nomor_hp, 'hadir');
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
                    ->placeholder('Masukkan alasan izin siswa...')
                    ->required()
                    ->rows(5),
            ])
            ->action(function(array $data, Model $record) {
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenSiswa::create([
                    'siswa_id' => $record->id,
                    'semester_id' => $semesterId,
                    'user_pengabsen' => auth()->user()->username,
                    'tipe_absen' => 'ashar',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'izin',
                    'keterangan' => $data['keterangan']
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Izin.')
                            ->success()
                            ->send();
                    $this->sendWhatsappWali($record->nama, $record->nomor_hp, 'izin');
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
                $absen = \App\Models\AbsenSiswa::create([
                    'siswa_id' => $record->id,
                    'semester_id' => $semesterId,
                    'user_pengabsen' => auth()->user()->username,
                    'tipe_absen' => 'ashar',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'sakit',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Sakit.')
                            ->success()
                            ->send();
                    $this->sendWhatsappWali($record->nama, $record->nomor_hp, 'sakit');
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
                $absen = \App\Models\AbsenSiswa::create([
                    'siswa_id' => $record->id,
                    'semester_id' => $semesterId,
                    'user_pengabsen' => auth()->user()->username,
                    'tipe_absen' => 'ashar',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'alpha',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Alpha.')
                            ->success()
                            ->send();
                    $this->sendWhatsappWali($record->nama, $record->nomor_hp, 'alpha');
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

    public function sendWhatsappWali(string $namaSiswa, int $phoneNumber, string $status)
    {
        $token = \App\Models\Pengaturan::first()->value('token_whatsapp');
        $user = \App\Models\User::whereIn('level', ['superadmin', 'admin'])->get();

        if($token === null) { return; }

        $fonnteApiUrl = 'https://api.fonnte.com/send'; // URL ini bisa berubah, cek dokumentasi FonNte terbaru
        if($status === 'hadir') {
            $message = "Assalamualaikum Wr.Wb\n";
            $message .= "Bapak/Ibu wali murid dari " . $namaSiswa . ".\n"; // Baris baru setelah nama siswa
            $message .= "Putra/putri Anda telah melakukan presensi hari ini:\n"; // Baris baru
            $message .= "- Tanggal: " . date('l, d M Y') . "\n"; // Baris baru
            $message .= "- Waktu: " .  date('H:i:s') . "\n"; // Baris baru
            $message .= "- Sholat: Dhuha \n"; // Baris baru
            $message .= "\n"; // Baris kosong untuk spasi
            $message .= "Terima kasih atas perhatiannya.\n";
            $message .= "\n *_Pesan otomatis tidak perlu dibalas._";
        } else {
            $message = "Assalamualaikum Wr.Wb\n";
            $message .= "Bapak/Ibu wali murid dari " . $namaSiswa . ".\n"; // Baris baru setelah nama siswa
            $message .= "Putra/putri Anda tidak melakukan presensi hari ini:\n"; // Baris baru
            $message .= "- Ketarangan: " . $status . "\n"; // Baris baru
            $message .= "- Sholat: Dhuha  \n"; // Baris baru
            $message .= "\n"; // Baris kosong untuk spasi
            $message .= "Terima kasih atas perhatiannya.\n";
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
