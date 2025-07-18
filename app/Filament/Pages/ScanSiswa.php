<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScanSiswa extends Page
{
    
    
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static string $view = 'filament.pages.scan-siswa';

    public static function canAccess(): bool
    {
        return auth()->user()->level !== 'siswa';
    }

    // public function table(Table $table): Table
    // {
    //     return $table
    //     ->query(
    //         AbsenSiswa::query()->where('tanggal', date('Y-m-d'))->orderBy('created_at', 'desc')->limit(10)
    //     )
    //     ->paginated(false)
    //     ->columns([
    //         Split::make([
    //             TextColumn::make('tanggal')->dateTime('l, m F Y')->label('tanggal'),
    //             TextColumn::make('siswa.nama'),
    //             TextColumn::make('tipe_absen')->badge()->color('success'),
    //             TextColumn::make('waktu')->badge()->color('info'),
    //         ])
    //     ])
    //     ->filters([])
    //     ->actions([]);
    // }

}
