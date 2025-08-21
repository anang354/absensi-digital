<?php

namespace App\Filament\Widgets;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UserChartWidget extends BaseWidget
{
    protected function getStats(): array
    {
        if(auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin') 
        {
            $guru = Guru::count();
            $siswa = Siswa::count();
            $kelas = Kelas::count();
        } else {
            $guru = Guru::where('jenjang', auth()->user()->guru->jenjang)->count();
            $siswa = Siswa::whereHas('kelas', function (Builder $query) {
                    $query->where('jenjang', auth()->user()->guru->jenjang);
                })->count();
            $kelas = Kelas::where('jenjang', auth()->user()->guru->jenjang)->count();
        }
        return [
            Stat::make('Total Guru', $guru)
                    ->description('Jumlah Guru Saat ini')
                    ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                    ->color('success'),
            Stat::make('Total Siswa', $siswa)
                    ->description('Jumlah Siswa Saat ini')
                    ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                    ->color('info'),
            Stat::make('Total Kelas', $kelas)
                    ->description('Jumlah Kelas Saat ini')
                    ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                    ->color('primary'),
            ];
    }

    protected function getHeading(): ?string
    {
        return 'Informasi User';
    }

    public static function canView(): bool 
    {
        return auth()->user()->level !== 'siswa';
    }
}
