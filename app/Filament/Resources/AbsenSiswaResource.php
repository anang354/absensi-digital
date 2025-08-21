<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\AbsenSiswa;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AbsenSiswaResource\Pages;
use App\Filament\Resources\AbsenSiswaResource\RelationManagers;

class AbsenSiswaResource extends Resource
{
    protected static ?string $model = AbsenSiswa::class;

    protected static ?string $navigationGroup = 'Absensi Siswa';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('l, d-m-Y'),
                TextColumn::make('siswa.nama')->searchable(),
                TextColumn::make('siswa.kelas.nama_kelas'),
                TextColumn::make('waktu')->time('H:i'),
                BadgeColumn::make('status')
                ->colors([
                    'success' => 'hadir',
                    'info' => 'izin',
                    'warning' => 'sakit',
                    'danger' => 'alpha',
                ]),
                TextColumn::make('user_pengabsen')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kelas')
                    ->relationship('siswa.kelas', 'nama_kelas') // Ini adalah kuncinya!
                    ->label('Filter Berdasarkan Kelas')
                    ->placeholder('Pilih Kelas')
                    ->options(
                        \App\Models\Kelas::pluck('nama_kelas', 'id')->toArray()
                    ),
                SelectFilter::make('status')
                    ->placeholder('Pilih Status')
                    ->multiple()
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListAbsenSiswas::route('/'),
            'create' => Pages\CreateAbsenSiswa::route('/create'),
            'edit' => Pages\EditAbsenSiswa::route('/{record}/edit'),
        ];
    }
}
