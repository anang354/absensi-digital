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
        if(auth()->user()->level !== 'kepsek') {
            $gurus = Guru::with(['absenGurus' => function ($query) {
                $query->whereBetween('tanggal_presensi', [$this->startDate, $this->endDate]);
            }])->get();
        } else {
            $jenjang = auth()->user()->guru->jenjang;
            $gurus = Guru::with(['absenGurus' => function ($query) {
                $query->whereBetween('tanggal_presensi', [$this->startDate, $this->endDate]);
            }])->where('jenjang', $jenjang)->get();
        }

        foreach ($gurus as $guru) {
            $row = [$guru->nama];

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
        $headings = ['Nama Guru'];

        foreach ($this->dates as $date) {
            $tanggal = $date->format('d M');
            $headings[] = $tanggal;
            $headings[] = ''; // Placeholder, nanti di-merge
        }

        return $headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Buat baris ke-2 sebagai subheadings (IN & OUT)
                $sheet = $event->sheet;
                $sheet->insertNewRowBefore(2, 1);
                $sheet->setCellValue('A2', ''); // Nama Guru kosong

                $colIndex = 2;
                foreach ($this->dates as $i => $date) {
                    $inCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $outCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

                    // Merge tanggal header
                    $sheet->mergeCells("{$inCol}1:{$outCol}1");

                    // Set subheading
                    $sheet->setCellValue("{$inCol}2", 'IN');
                    $sheet->setCellValue("{$outCol}2", 'OUT');

                    // Align center
                    $sheet->getStyle("{$inCol}1:{$outCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $colIndex += 2;
                }

                // Set 'Nama Guru' title merge (row 1 dan 2)
                $sheet->mergeCells('A1:A2');
                $sheet->getStyle('A1:A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}
