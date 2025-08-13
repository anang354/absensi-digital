<?php

namespace App\Filament\Resources\AbsenSiswaResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\AbsenSiswa;
use Filament\Actions\Action;
use App\Exports\AbsenSiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Resources\Components\Tab;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AbsenSiswaResource;

class ListAbsenSiswas extends ListRecords
{
    protected static string $resource = AbsenSiswaResource::class;

    protected ?string $subheading = 'Data yang ditampilkan adalah satu minggu terakhir.';

    public function getTabs(): array
    {
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        $tanggalMulai = Carbon::today()->subDays(6);
        $tanggalAkhir = Carbon::today()->endOfDay();
        if(auth()->user()->level == 'guru' || auth()->user()->level == 'kepsek'){
            return [
                        AbsenSiswa::ABSEN_DHUHA => Tab::make()
                            ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_DHUHA)->where('semester_id', $semesterId)
                            ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                            ->whereHas('siswa.kelas', fn ($q) => $q->where('jenjang', auth()->user()->guru->jenjang))
                            ->orderByDesc('created_at')),
                        AbsenSiswa::ABSEN_DZUHUR => Tab::make()
                            ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_DZUHUR)->where('semester_id', $semesterId)
                            ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                            ->whereHas('siswa.kelas', fn ($q) => $q->where('jenjang', auth()->user()->guru->jenjang))
                            ->orderByDesc('created_at')),
                        AbsenSiswa::ABSEN_ASHAR => Tab::make()
                            ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_ASHAR)->where('semester_id', $semesterId)
                            ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                            ->whereHas('siswa.kelas', fn ($q) => $q->where('jenjang', auth()->user()->guru->jenjang))
                            ->orderByDesc('created_at')),
                    ];
        } else {
            return [
                AbsenSiswa::ABSEN_DHUHA => Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_DHUHA)->where('semester_id', $semesterId)
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                    ->orderByDesc('created_at')),
                AbsenSiswa::ABSEN_DZUHUR => Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_DZUHUR)->where('semester_id', $semesterId)
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                    ->orderByDesc('created_at')),
                AbsenSiswa::ABSEN_ASHAR => Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_ASHAR)->where('semester_id', $semesterId)
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                    ->orderByDesc('created_at')),
            ];
        }
    }
    public function getDefaultActiveTab(): string | int | null
    {
        return AbsenSiswa::ABSEN_DHUHA;
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('download_data_absensi')
                ->label('Download Data Absensi')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('kelas_id')
                    ->required()
                    ->options(function() {
                        if(auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin') {
                            return \App\Models\Kelas::pluck('nama_kelas', 'id')->toArray();
                        } else {
                            return \App\Models\Kelas::where('jenjang', auth()->user()->guru->jenjang)->pluck('nama_kelas', 'id')->toArray();
                        }
                    }),
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('tanggal_akhir')
                        ->label('Tanggal Akhir')
                        ->required(),
                ])
                ->action(function (array $data) {
                    if ($data['tanggal_mulai'] > $data['tanggal_akhir']) {
                        Notification::make()
                            ->title('Tanggal tidak valid')
                            ->danger()
                            ->send();

                        return;
                    }
                    $fileName = 'rekap_absen_siswa_' . now()->format('Ymd_His') . '.xlsx';

                return Excel::download(
                    new AbsenSiswaExport(
                        $data['kelas_id'],
                        Carbon::parse($data['tanggal_mulai']),
                        Carbon::parse($data['tanggal_akhir']),
                    ),
                    $fileName
                );
                })
        ];
    }
}
