<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AbsenSiswa;
use Illuminate\Database\Eloquent\Builder;

class AbsenAsharChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Absen Ashar';

    public static function canView(): bool 
    {
        return auth()->user()->level !== 'siswa';
    }

    protected function getData(): array
    {
         
        $todayAttendance = AbsenSiswa::query()
            ->select('status')
            ->whereDate('tanggal', date('Y-m-d'))
            ->where('tipe_absen', 'ashar')
            ->get();
        //dd($todayAttendance);
         // Mengelompokkan dan menghitung jumlah setiap tipe
        $attendanceCounts = $todayAttendance->countBy('status');

            $hadir = $attendanceCounts->get('hadir', 0);
            $izin = $attendanceCounts->get('izin', 0);
            $sakit = $attendanceCounts->get('sakit', 0);
            $alpha = $attendanceCounts->get('alpha', 0);
        
        
        return [
                'datasets' => [
                        [
                            'data' => [$hadir, $izin, $sakit, $alpha],
                            'backgroundColor' => [
                                'rgb(34, 197, 94)',
                                'rgb(59, 130, 246)',
                                'rgb(245, 158, 11)',
                                '#F05252',
                            ],
                        ],
                    ],
                'labels' => ['Hadir', 'Izin', 'Sakit', 'Alpha'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
