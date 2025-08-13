<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Kelas;
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

class AbsenDzuhur extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Absensi Siswa';

    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.absen-dzuhur';

    public static function canAccess(): bool
    {
        return auth()->user()->level === 'kepsek' || auth()->user()->level === 'guru' || auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin';
    }

    public function table(Table $table): Table
    {
        if(auth()->user()->level === 'guru' || auth()->user()->level === 'kepsek') {
            $queryAbsen = Siswa::query()
                    ->whereDoesntHave('absenSiswa', function (Builder $subQuery) {
                        $subQuery->whereDate('tanggal', Carbon::today());
                        $subQuery->where('tipe_absen', AbsenSiswa::ABSEN_DZUHUR);
                    })
                    ->whereHas('user')
                    ->whereHas('kelas', function (Builder $query) {
                        $query->where('jenjang', auth()->user()->guru->jenjang);
                    });
        } else {
            $queryAbsen = Siswa::query()
                    ->whereDoesntHave('absenSiswa', function (Builder $subQuery) {
                        $subQuery->whereDate('tanggal', Carbon::today());
                        $subQuery->where('tipe_absen', AbsenSiswa::ABSEN_DZUHUR);
                    })
                    ->whereHas('user');
        }
        return $table
        ->query(
            $queryAbsen
        )
        ->columns([
            TextColumn::make('nisn')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nama')->searchable(),
            TextColumn::make('kelas.nama_kelas'),
            TextColumn::make('jenis_kelamin')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nomor_hp')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('wali'),
        ])
        ->headerActions([
            Action::make('laporan-dzuhur')
            ->label('Laporan Sholat dzuhur')
            ->url('/admin/laporan-dzuhur-siswa')
            ->color('success')
            ->icon('heroicon-o-document'),
        ])
        ->filters([
            SelectFilter::make('kelas_id')
                    ->label('Filter Berdasarkan Kelas')
                    ->placeholder('Pilih Kelas')
                    ->options(
                       function (): array { // <<< INI KUNCI UTAMA >>>
                    // Mendapatkan user yang sedang login
                    $user = auth()->user();

                    // Kondisi 1: Jika user adalah 'admin' atau 'super_admin'
                    if ($user->level === 'admin' || $user->level === 'superadmin') {
                        // Tampilkan semua kelas
                        return Kelas::pluck('nama_kelas', 'id')->toArray();
                    }

                    // Kondisi 2: Jika user adalah 'guru' atau 'kepsek'
                    // Asumsi: user memiliki relasi hasOne ke model Guru
                    // Asumsi: model Guru memiliki kolom 'jenjang'
                    if ($user->level === 'guru' || $user->level === 'kepsek') {
                        $jenjangGuru = $user->guru->jenjang; // Ambil jenjang guru yang sedang login
                        
                        // Tampilkan kelas yang sesuai dengan jenjang guru tersebut
                        return Kelas::where('jenjang', $jenjangGuru)
                                     ->pluck('nama_kelas', 'id')
                                     ->toArray();
                    }
                    
                    // Kondisi fallback: Jika user tidak memiliki peran yang relevan, kembalikan array kosong
                    return [];
                }
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
                    'tipe_absen' => 'dzuhur',
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
                    'tipe_absen' => 'dzuhur',
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
                    'tipe_absen' => 'dzuhur',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'sakit',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Sakit.')
                            ->success()
                            ->send();
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
                    'tipe_absen' => 'dzuhur',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'alpha',
                ]);
                if($absen) {
                    Notification::make()
                            ->title('Berhasil!')
                            ->body('Absensi ' . $record->nama . ' berhasil dicatat sebagai Alpha.')
                            ->success()
                            ->send();
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
}
