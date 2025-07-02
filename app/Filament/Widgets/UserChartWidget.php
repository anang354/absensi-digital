<?php

namespace App\Filament\Widgets;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UserChartWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Guru', Guru::count())
                    ->description('Jumlah Guru Saat ini')
                    ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                    ->color('success'),
            Stat::make('Total Siswa', Siswa::count())
                    ->description('Jumlah Siswa Saat ini')
                    ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                    ->color('info'),
            Stat::make('Total Kelas', Kelas::count())
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
