<?php

namespace App\Exports;

use App\Models\Guru;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbsenGuruExport implements FromArray, WithHeadings, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $dates;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->dates = CarbonPeriod::create($this->startDate, $this->endDate);
    }

    public function array(): array
    {
        $rows = [];

        $gurusQuery = Guru::with(['absenGurus' => function ($query) {
            $query->whereBetween('tanggal_presensi', [$this->startDate, $this->endDate]);
        }]);

        // Filter berdasarkan jenjang jika user level kepsek
        if (auth()->user()->level === 'kepsek') {
            $jenjang = auth()->user()->guru->jenjang ?? null;
            $gurusQuery->where('jenjang', $jenjang);
        }

        $gurus = $gurusQuery->get();

        foreach ($gurus as $guru) {
            $row = [
                $guru->nama,
                $guru->jenjang ?? '-',
            ];

            foreach ($this->dates as $date) {
                $absen = $guru->absenGurus->firstWhere('tanggal_presensi', $date->toDateString());

                $row[] = $absen?->checkin ?? '-';
                $row[] = $absen?->checkout ?? '-';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Nama Guru', 'Jenjang'];

        foreach ($this->dates as $date) {
            $tanggal = $date->format('d M');
            $headings[] = $tanggal;
            $headings[] = ''; // Placeholder kolom OUT
        }

        return $headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Sisipkan subheader (row ke-2)
                $sheet->insertNewRowBefore(2, 1);

                // Baris subheading: IN / OUT
                $sheet->setCellValue('A2', '');
                $sheet->setCellValue('B2', '');

                $colIndex = 3; // Mulai dari kolom C
                foreach ($this->dates as $date) {
                    $inCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $outCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

                    // Merge header tanggal
                    $sheet->mergeCells("{$inCol}1:{$outCol}1");
                    $sheet->setCellValue("{$inCol}2", 'IN');
                    $sheet->setCellValue("{$outCol}2", 'OUT');

                    // Style
                    $sheet->getStyle("{$inCol}1:{$outCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $colIndex += 2;
                }

                // Merge dan style Nama Guru & Jenjang
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->getStyle('A1:B2')->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Apply border and background
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Background header
                $sheet->getStyle("A1:{$highestColumn}2")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            }
        ];
    }
}
