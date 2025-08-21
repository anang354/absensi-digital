<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use App\Models\Siswa;

class AbsenSiswaExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $kelasId, $tanggalMulai, $tanggalAkhir;
    protected int $counter = 1; // Untuk nomor urut

    public function __construct($kelasId, $tanggalMulai, $tanggalAkhir)
    {
        $this->kelasId = $kelasId;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function collection()
    {
        return Siswa::with(['kelas', 'absenSiswa' => function ($query) {
                $query->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalAkhir]);
            }])
            ->where('kelas_id', $this->kelasId)
            ->get();
    }

    public function map($siswa): array
    {
        $dhuha = $siswa->absenSiswa->where('tipe_absen', 'dhuha');
        $dzuhur = $siswa->absenSiswa->where('tipe_absen', 'dzuhur');
        $ashar = $siswa->absenSiswa->where('tipe_absen', 'ashar');

        return [
            $this->counter++,
            $siswa->nama,
            optional($siswa->kelas)->nama_kelas,
            $siswa->jenis_kelamin,

            // Dhuha
            $dhuha->where('status', 'hadir')->count(),
            $dhuha->where('status', 'izin')->count(),
            $dhuha->where('status', 'sakit')->count(),
            $dhuha->where('status', 'alpha')->count(),

            // Dzuhur
            $dzuhur->where('status', 'hadir')->count(),
            $dzuhur->where('status', 'izin')->count(),
            $dzuhur->where('status', 'sakit')->count(),
            $dzuhur->where('status', 'alpha')->count(),

            // Ashar
            $ashar->where('status', 'hadir')->count(),
            $ashar->where('status', 'izin')->count(),
            $ashar->where('status', 'sakit')->count(),
            $ashar->where('status', 'alpha')->count(),
        ];
    }

    public function headings(): array
    {
        return [
            [
                'No', 'Nama Siswa', 'Kelas', 'Jenis Kelamin',
                'Absen Dhuha', '', '', '',
                'Absen Dzuhur', '', '', '',
                'Absen Ashar', '', '', '',
            ],
            [
                '', '', '', '',
                'Hadir', 'Izin', 'Sakit', 'Alpha',
                'Hadir', 'Izin', 'Sakit', 'Alpha',
                'Hadir', 'Izin', 'Sakit', 'Alpha',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge header
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells('E1:H1');  // Absen Dhuha
                $sheet->mergeCells('I1:L1');  // Absen Dzuhur
                $sheet->mergeCells('M1:P1');  // Absen Ashar

                // Center align & Bold header
                $sheet->getStyle('A1:P2')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'D9E1F2', // Warna biru muda
                        ],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Set border untuk seluruh data
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A3:P' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}