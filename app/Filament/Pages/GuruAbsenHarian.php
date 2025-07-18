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
