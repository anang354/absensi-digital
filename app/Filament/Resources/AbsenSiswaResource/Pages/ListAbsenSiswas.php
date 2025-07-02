<?php

namespace App\Filament\Resources\AbsenSiswaResource\Pages;

use Filament\Actions;
use App\Models\AbsenSiswa;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AbsenSiswaResource;

class ListAbsenSiswas extends ListRecords
{
    protected static string $resource = AbsenSiswaResource::class;

    protected ?string $subheading = 'Data yang ditampilkan adalah berdasarkan semester yang sedang aktif.';

    public function getTabs(): array
    {
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        return [
            AbsenSiswa::ABSEN_DHUHA => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_DHUHA)->where('semester_id', $semesterId)->orderByDesc('created_at')),
            AbsenSiswa::ABSEN_ASHAR => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_absen', AbsenSiswa::ABSEN_ASHAR)->where('semester_id', $semesterId)->orderByDesc('created_at')),
        ];
    }
    public function getDefaultActiveTab(): string | int | null
    {
        return AbsenSiswa::ABSEN_DHUHA;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
