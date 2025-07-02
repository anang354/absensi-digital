<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\AbsenSiswa;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AbsenSiswaChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = 'Absensi Siswa';

    public static function canView(): bool 
    {
        return auth()->user()->level === 'siswa';
    }

    protected function getData(): array
    {
        $tipe = $this->filters['absensi'];
        $start = $this->filters['tanggal_awal'];
        $end = $this->filters['tanggal_akhir'];

        $startDate = $start ? Carbon::parse($start) : now()->startOfMonth();
        $endDate = $end ? Carbon::parse($end) : now();

        $todayAttendance = AbsenSiswa::query()
            ->select('status')
            //->whereDate('tanggal', date('Y-m-d'))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('tipe_absen', $tipe)
            ->where('siswa_id', auth()->user()->siswa->id)
            ->get();
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
        return 'pie';
    }
}
