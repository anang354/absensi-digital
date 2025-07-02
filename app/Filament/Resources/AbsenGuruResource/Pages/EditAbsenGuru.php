<?php

namespace App\Filament\Resources\AbsenGuruResource\Pages;

use App\Filament\Resources\AbsenGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbsenGuru extends EditRecord
{
    protected static string $resource = AbsenGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
