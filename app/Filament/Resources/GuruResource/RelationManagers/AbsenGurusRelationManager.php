<?php

namespace App\Filament\Resources\GuruResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AbsenGurusRelationManager extends RelationManager
{
    protected static string $relationship = 'absenGurus';
     protected static ?string $title = 'Riwayat Presensi Guru';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_presensi')->date('l, d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin'),
                Tables\Columns\TextColumn::make('checkout'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'hadir' => 'success',
                    'sakit' => 'warning',
                    'izin' => 'info',
                    'alpha' => 'danger',
                }),
            ])
            ->filters([
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
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
