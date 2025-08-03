<?php

namespace App\Filament\Widgets;

use App\Models\AbsenSiswa;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class AbsenDhuhaChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Absen Dhuha';

    public static function canView(): bool 
    {
        return auth()->user()->level !== 'siswa';
    }

    protected function getData(): array
    {
        if(auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin') {
            $todayAttendance = AbsenSiswa::query()
            ->select('status')
            ->whereDate('tanggal', date('Y-m-d'))
            ->where('tipe_absen', 'dhuha')
            ->get();
        } else {
            $todayAttendance = AbsenSiswa::query()
            ->select('status')
            ->whereDate('tanggal', date('Y-m-d'))
            ->where('tipe_absen', 'dhuha')
            ->whereHas('siswa.kelas', function (Builder $query) {
                $query->where('jenjang', auth()->user()->guru->jenjang);
            })
            ->get();
        }
        
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
                            'label' => 'Blog posts created',
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
