<?php

namespace App\Filament\Resources\AbsenGuruResource\Pages;

use Carbon\Carbon;
use App\Models\Guru;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AbsenGuruResource;
use Filament\Resources\Pages\ListRecords\Tab;
use Maatwebsite\Excel\Facades\Excel; // jika pakai Excel export
use App\Exports\AbsenGuruExport; // export custom kamu

class ListAbsenGurus extends ListRecords
{
    protected static string $resource = AbsenGuruResource::class;
    
    protected ?string $subheading = 'Data yang ditampilkan berdasarkan semester yang sedang aktif.';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Action::make('export_absen')
                ->label('Download Data Absensi')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Validasi tanggal
                    if ($data['start_date'] > $data['end_date']) {
                        Notification::make()
                            ->title('Tanggal tidak valid')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Export data (contoh dengan Laravel Excel)
                    return Excel::download(
                        new AbsenGuruExport($data['start_date'], $data['end_date']),
                        'absensi-guru-' . now()->format('Ymd_His') . '.xlsx'
                    );
                }),
        ];
    }

}
