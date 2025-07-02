<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\AbsenGuru;
use Filament\Tables\Table;  
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Concerns\InteractsWithTable;

class GuruScan extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $model = AbsenGuru::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera'; // Ikon untuk halaman ini
    protected static string $view = 'filament.pages.guru-scan';

    protected static ?string $title = 'Take Absen'; // Judul yang tampil di UI Filament
    protected static ?string $slug = 'guru-scan'; // Bagian URL: /admin/guru-scan

    public $scannedData = '';

    public static function canAccess(): bool
    {
        return auth()->user()->level === 'guru' || auth()->user()->level === 'admin';
    }
    public function table(Table $table): Table
    {
        return $table
        ->query(
            AbsenGuru::query()->where('guru_id', auth()->user()->guru->id)->orderBy('created_at', 'desc')->limit(5)
        )
        ->paginated(false)
        ->columns([
            Split::make([
                TextColumn::make('tanggal_presensi')->dateTime('l, m F Y'),
                TextColumn::make('checkin')->badge()->color('success'),
                TextColumn::make('checkout')->badge()->color('info'),
            ])
        ])
        ->filters([])
        ->actions([]);
    }

   

}