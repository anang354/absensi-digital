<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class Dashboard extends \Filament\Pages\Dashboard
{
    // ...
    use HasFiltersForm;

    public $defaultAction = 'checkSemester';

    public function checkSemester(): Action
    {
        $semester = \App\Models\Semester::where('is_active', true)->count();
        $pengaturan = \App\Models\Pengaturan::select('longitude', 'latitude')->first();
        $stringHtml = '';
        $isVisible = false;
        if(auth()->user()->level !== 'siswa') {
            if(!$pengaturan || $pengaturan->longitude === "" && $pengaturan->latitude === "") {
                $isVisible = true;
                $stringHtml .= "<li>Anda belum mengatur lokasi latitude dan longitude sekolah, ini dapat mempengaruhi absensi guru! Segera atur pada menu <strong>Pengaturan</strong></li>";
            }
            if($semester === 0 ) {
                $isVisible = true;
                $stringHtml .= "<li>Tidak ada Semester aktif! ini dapat mempengaruhi absensi siswa! Segera atur pada menu <strong>Semester</strong>";
            }
        }

        return Action::make('checkSemester')->visible($isVisible)
        ->modalSubmitActionLabel('Ok')
        ->color('danger')
        ->modalIcon('heroicon-o-exclamation-triangle')
        ->modalHeading('Peringatan Aplikasi')
        ->modalDescription(new HtmlString($stringHtml));
    }

    public function filtersForm(Form $form) 
    {
        if(auth()->user()->level === "siswa") {
            return $form->schema([
               Section::make('')->schema([
                 Select::make('absensi')->options(\App\Models\AbsenSiswa::TIPE_ABSEN),
                DatePicker::make('tanggal_awal'),
                DatePicker::make('tanggal_akhir'),
               ])->columns(3)
            ]);
        } else {
            return $form->schema([]);
        }
    }

}