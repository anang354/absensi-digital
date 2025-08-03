<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KelasResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KelasResource\RelationManagers;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_kelas')->required(),
                Radio::make('jenjang')
                ->options(Kelas::JENJANG_SEKOLAH)
                ->live()
                ->afterStateUpdated(fn (Forms\Set $set) => $set('level_kelas', null)),
                Select::make('level_kelas')->required()
                ->options(function (Forms\Get $get): array { // Menggunakan closure untuk opsi
                            $jenjang = $get('jenjang'); // Mengambil nilai jenjang pendidikan yang dipilih

                            // Logika untuk menentukan opsi kelas berdasarkan jenjang
                            if ($jenjang === Kelas::JENJANG_SMP) {
                                return [
                                    '7' => 'Kelas 7',
                                    '8' => 'Kelas 8',
                                    '9' => 'Kelas 9',
                                ];
                            } elseif ($jenjang === Kelas::JENJANG_SMK) {
                                return [
                                    '10' => 'Kelas 10',
                                    '11' => 'Kelas 11',
                                    '12' => 'Kelas 12',
                                ];
                            }
                            // Kembalikan array kosong jika jenjang belum dipilih atau tidak cocok
                            return [];
                        }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('kelas_id')->color('danger'),
                TextColumn::make('nama_kelas'),
                TextColumn::make('level_kelas'),
                TextColumn::make('jenjang'),
                TextColumn::make('siswas_count') // Ini adalah kuncinya!
                    ->label('Jumlah Siswa')
                    ->counts('siswas')
            ])
            ->filters([
                //
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
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
