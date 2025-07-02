<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\AbsenGuru;
use Filament\Tables\Table;
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
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guru.nama'),
                TextColumn::make('tanggal_presensi'),
                TextColumn::make('checkin'),
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
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
                // Tambahkan where clause untuk memfilter berdasarkan semester_id
                return $query->where('semester_id', $semesterId)->orderByDesc('created_at');
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
