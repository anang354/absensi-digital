<?php

namespace App\Filament\Resources\AbsenGuruResource\Pages;

use Carbon\Carbon;
use App\Models\Guru;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AbsenGuruResource;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Support\Facades\DB;

class ListAbsenGurus extends ListRecords
{
    protected static string $resource = AbsenGuruResource::class;
    
    protected ?string $subheading = 'Data yang ditampilkan berdasarkan semester yang sedang aktif.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

}
