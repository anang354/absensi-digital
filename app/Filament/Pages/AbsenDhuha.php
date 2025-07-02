<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Siswa;
use Filament\Pages\Page;
use App\Models\AbsenSiswa;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Concerns\InteractsWithTable;

class AbsenDhuha extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Absensi Siswa';

    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.absen-dhuha';

    public static function canAccess(): bool
    {
        return auth()->user()->level === 'guru' || auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin';
    }


    public function table(Table $table): Table
    {
        return $table
        ->query(
            Siswa::query()
                    ->whereDoesntHave('absenSiswa', function (Builder $subQuery) {
                        $subQuery->whereDate('tanggal', Carbon::today());
                        $subQuery->where('tipe_absen', AbsenSiswa::ABSEN_DHUHA);
                    })
                    ->whereHas('user')
        )
        ->columns([
            TextColumn::make('nisn'),
            TextColumn::make('nama'),
            TextColumn::make('jenis_kelamin'),
            TextColumn::make('nomor_hp'),
            TextColumn::make('wali'),
        ])
        ->filters([])
        ->actions([
            Action::make('Hadir')
            ->color('success')
            ->action(function(Model $record){
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $absen = \App\Models\AbsenSiswa::create([
                    'siswa_id' => $record->id,
                    'semester_id' => $semesterId,
                    'user_pengabsen' => auth()->user()->username,
                    'tipe_absen' => 'dhuha',
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
                    'tipe_absen' => 'dhuha',
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
                    'tipe_absen' => 'dhuha',
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
                    'tipe_absen' => 'dhuha',
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
