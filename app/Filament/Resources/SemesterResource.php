<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Semester;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SemesterResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SemesterResource\RelationManagers;
use App\Jobs\DeleteAbsensiBySemester; // Import Job yang sudah dibuat

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('semester')
                ->options([
                    'ganjil' => 'Ganjil',
                    'genap' => 'Genap'
                ])->required(),
                Forms\Components\Select::make('tahun')
                ->label('Tahun Ajaran')
                ->options([
                    '2024/2025' => '2024/2025',
                    '2025/2026' => '2025/2026',
                    '2026/2027' => '2026/2027',
                    '2027/2028' => '2027/2028',
                    '2028/2029' => '2028/2029',
                    '2029/2030' => '2029/2030',
                ])->required(),
                Forms\Components\DatePicker::make('tanggal_mulai')
                ->native(true),
                Forms\Components\DatePicker::make('tanggal_berakhir')
                ->native(true),
                Forms\Components\Radio::make('is_active')->required()->boolean()->default(false)->hidden(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('semester'),
                Tables\Columns\TextColumn::make('tahun')->label('Tahun Ajaran'),
                Tables\Columns\TextColumn::make('tanggal_mulai')->date('d M Y')->toggleable(),
                Tables\Columns\TextColumn::make('tanggal_berakhir')->date('d M Y')->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')
                ->label('Semester Aktif')
                ->onColor('success')
                ->afterStateUpdated(function (Tables\Columns\ToggleColumn $column, $state, $record) {
                        // Hanya jalankan jika toggle diatur ke TRUE
                        if ($state === true) {
                            // Perbarui semua record Semester lainnya menjadi is_active = false
                            // Kecuali record yang sedang diupdate ($record->id)
                            \App\Models\Semester::where('id', '!=', $record->id)
                                    ->update(['is_active' => false]);
                            $recipient = auth()->user();
                            Notification::make()
                                ->info()
                                ->title(auth()->user()->name.' telah merubah semester aktif menjadi semester '.$record->semester.' '.$record->tahun)
                                ->sendToDatabase($recipient);
                        }
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('delete_absensi')
                    ->label('Hapus Data Absensi')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation() // Menampilkan modal konfirmasi standar Filament
                    ->modalHeading(fn (Semester $record) => 'Hapus Data Absensi Semester ' . $record->nama_semester . '?')
                    ->modalDescription('Tindakan ini akan menghapus SEMUA data presensi guru dan siswa yang terkait dengan semester ini. Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Sekarang')
                    ->action(function (Semester $record) {
                        // Dispatch Job ke queue
                        DeleteAbsensiBySemester::dispatch($record->id, auth()->user()->id);

                        // Beri notifikasi ke user bahwa proses dimulai di background
                        Notification::make()
                            ->title('Penghapusan data absensi dimulai.')
                            ->body('Proses akan berjalan di latar belakang. Anda akan menerima notifikasi jika selesai atau ada kesalahan.')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit' => Pages\EditSemester::route('/{record}/edit'),
        ];
    }
}
