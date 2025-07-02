<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    // ...
    use HasFiltersForm;

    public function filtersForm(Form $form) 
    {
        if(auth()->user()->level === "siswa") {
            return $form->schema([
               Section::make('')->schema([
                 Select::make('absensi')->options([
                    'dhuha' => 'Dhuha',
                    'ashar' => 'Ashar'
                ]),
                DatePicker::make('tanggal_awal'),
                DatePicker::make('tanggal_akhir'),
               ])->columns(3)
            ]);
        } else {
            return $form->schema([]);
        }
    }

}