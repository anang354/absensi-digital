<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'admin' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('level', ['admin', 'superadmin']))
                ->badge(
                    $this->getResource()::getModel()::whereIn('level', ['admin', 'superadmin'])->count()
                )->badgeColor('success'),
            'guru' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('level', 'guru'))
                ->badge(
                    $this->getResource()::getModel()::where('level', 'guru')->count()
                )->badgeColor('info'),
            'siswa' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('level', 'siswa'))
                ->badge(
                    $this->getResource()::getModel()::where('level', 'siswa')->count()
                )->badgeColor('danger'),
        ];
    }
}
