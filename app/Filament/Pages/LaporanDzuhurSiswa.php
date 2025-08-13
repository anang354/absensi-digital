<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Kelas;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class LaporanDzuhurSiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.laporan-dzuhur-siswa';

     protected static bool $shouldRegisterNavigation = false;

     public static function canAccess() : bool 
    {
        return auth()->user()->level !== 'siswa';
    }

    public function table(Table $table): Table
    {
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        if(auth()->user()->level === 'guru' || auth()->user()->level === 'kepsek') {
            $queryDzuhur = \App\Models\Siswa::query()
                            ->whereHas('kelas', fn (Builder $query) => $query->where('jenjang',auth()->user()->guru->jenjang))
                            ->withCount(['absenSiswa as hadir' => fn (Builder $query) => $query->where('status', 'hadir')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)])
                            // Menghitung jumlah 'izin'
                            ->withCount(['absenSiswa as izin' => fn (Builder $query) => $query->where('status', 'izin')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)])
                            // Menghitung jumlah 'sakit'
                            ->withCount(['absenSiswa as sakit' => fn (Builder $query) => $query->where('status', 'sakit')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)])
                            // Menghitung jumlah 'alpha'
                            ->withCount(['absenSiswa as alpha' => fn (Builder $query) => $query->where('status', 'alpha')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)]);
        } else {
            $queryDzuhur = \App\Models\Siswa::query()
                        ->withCount(['absenSiswa as hadir' => fn (Builder $query) => $query->where('status', 'hadir')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)])
                        // Menghitung jumlah 'izin'
                        ->withCount(['absenSiswa as izin' => fn (Builder $query) => $query->where('status', 'izin')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)])
                        // Menghitung jumlah 'sakit'
                        ->withCount(['absenSiswa as sakit' => fn (Builder $query) => $query->where('status', 'sakit')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)])
                        // Menghitung jumlah 'alpha'
                        ->withCount(['absenSiswa as alpha' => fn (Builder $query) => $query->where('status', 'alpha')->where('tipe_absen', 'dzuhur')->where('semester_id', $semesterId)]);
        
        }
        return $table
        ->query($queryDzuhur)
        ->columns([
            TextColumn::make('nama')->searchable(),
            TextColumn::make('kelas.nama_kelas'),
            TextColumn::make('kelas.jenjang')
            ->label('Jenjang')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('jenis_kelamin'),
            TextColumn::make('hadir'),
            TextColumn::make('izin'),
            TextColumn::make('sakit'),
            TextColumn::make('alpha'),
        ])
        ->filters([
            SelectFilter::make('kelas')
            ->relationship('kelas', 'nama_kelas')
        ])
        ->actions([]);
    }
}
