<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\AbsenGuru;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\AbsenGuruResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AbsenGuruResource\RelationManagers;

class AbsenGuruResource extends Resource
{
    protected static ?string $model = AbsenGuru::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Absensi Guru';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Placeholder::make('guru.nama')
                    ->label('Nama Guru')
                    ->content(fn (?AbsenGuru $record) => $record ? $record->guru->nama : 'N/A'),
                 Forms\Components\Select::make('status') // 'status' adalah nama kolom di database Anda
                ->label('Status Kehadiran') // Label yang akan tampil di form
                ->options([
                    'hadir' => 'Hadir',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'alpha' => 'Alpha',
                ])
                ->required() // Opsional: membuat field ini wajib diisi
                ->native(false),
                Forms\Components\TextInput::make('keterangan'),
               Forms\Components\Section::make('Foto Selfie')->schema([
                    Forms\Components\FileUpload::make('foto_in')->disk('public')->image()
                    ->deletable(false),  
                    Forms\Components\FileUpload::make('foto_out')->disk('public')->image()
                    ->deletable(false)
               ])->columns(2)  
            ]);
    }

    public static function infolist(Infolist $infolist) : Infolist 
    {
        return $infolist
        ->schema([
            \Filament\Infolists\Components\Section::make('')
            ->columns([
                'sm' => 3,
                'xl' => 6,
                '2xl' => 8,
            ])
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('guru.nama')->columnSpan([
                'sm' => 8,
                'xl' => 8,
                '2xl' => 8,
            ]),
                \Filament\Infolists\Components\TextEntry::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'hadir' => 'success',
                    'sakit' => 'warning',
                    'izin' => 'info',
                    'alpha' => 'danger',
                })->columnSpan([
                'sm' => 2,
                'xl' => 3,
                '2xl' => 4,
            ]),
                \Filament\Infolists\Components\TextEntry::make('keterangan')->columnSpan([
                'sm' => 2,
                'xl' => 3,
                '2xl' => 4,
            ]),
                \Filament\Infolists\Components\TextEntry::make('checkin')->columnSpan([
                'sm' => 2,
                'xl' => 3,
                '2xl' => 4,
            ]),
                \Filament\Infolists\Components\TextEntry::make('checkout')->columnSpan([
                'sm' => 2,
                'xl' => 3,
                '2xl' => 4,
            ]),
                \Filament\Infolists\Components\ImageEntry::make('foto_in')->disk('public')->columnSpan([
                'sm' => 2,
                'xl' => 3,
                '2xl' => 4,
            ]),
                \Filament\Infolists\Components\ImageEntry::make('foto_out')->disk('public')->columnSpan([
                'sm' => 2,
                'xl' => 3,
                '2xl' => 4,
            ]),
            ])
            
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guru.nama')->searchable(),
                TextColumn::make('tanggal_presensi'),
                TextColumn::make('checkin')
                ->color(function (AbsenGuru $record) {
                        // Jika ada absen dan checkin, periksa apakah terlambat
                        if ($record->checkin) {
                            $checkinTime = Carbon::parse($record->checkin);
                            $batasWaktu = Carbon::parse('07:30');

                            if ($checkinTime->greaterThan($batasWaktu)) {
                                return 'danger'; // Merah jika lewat dari 07:30
                            }
                        }
                        return 'default'; // Warna default jika tepat waktu atau tidak ada data
                    }),
                TextColumn::make('checkout'),
                TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'hadir' => 'success',
                    'sakit' => 'warning',
                    'izin' => 'info',
                    'alpha' => 'danger',
                }),
                TextColumn::make('keterangan')->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                SelectFilter::make('status')
                    ->placeholder('Pilih Status')
                    ->multiple()
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ]),
                SelectFilter::make('bulan') // Nama internal filter
                    ->options([
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                    ])
                    ->default('this_month') // Defaultkan ke 'Bulan Ini'
                    ->query(function (Builder $query, array $data): Builder {
                        // Ambil bulan dan tahun saat ini
                        $currentMonth = Carbon::now()->month;
                        $currentYear = Carbon::now()->year;

                        if (isset($data['value'])) {
                            if ($data['value'] === 'this_month') {
                                return $query->whereMonth('tanggal_presensi', $currentMonth)
                                             ->whereYear('tanggal_presensi', $currentYear);
                            } elseif ($data['value'] === 'last_month') {
                                $lastMonth = Carbon::now()->subMonth(); // Ambil bulan sebelumnya
                                return $query->whereMonth('tanggal_presensi', $lastMonth->month)
                                             ->whereYear('tanggal_presensi', $lastMonth->year);
                            }
                        }
                        // Jika tidak ada filter yang dipilih (atau default), tetap tampilkan bulan ini
                        return $query->whereMonth('tanggal_presensi', $currentMonth)
                                     ->whereYear('tanggal_presensi', $currentYear);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver()->color('info'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                $user =  auth()->user();

                if($user->level !== 'kepsek') {
                    return $query->where('semester_id', $semesterId)->orderByDesc('created_at');
                } else {
                    $jenjang = $user->guru->jenjang;
                    return $query->where('semester_id', $semesterId)
                    ->whereHas('guru', function($query) use ($jenjang) {
                        $query->where('jenjang', $jenjang);
                    })
                    ->orderByDesc('created_at');
                }
                
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsenGurus::route('/'),
            'create' => Pages\CreateAbsenGuru::route('/create'),
            'edit' => Pages\EditAbsenGuru::route('/{record}/edit'),
        ];
    }
}
