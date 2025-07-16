<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\SetorHafalan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class PantauanHafalanSiswa extends Page implements HasTable
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.pantauan-hafalan-siswa';

    use InteractsWithTable;

    protected static ?string $model = SetorHafalan::class;

    public static function canAccess(): bool
    {
        return auth()->user()->level === 'siswa';
    }

    protected function getSemester()
    {
        return \App\Models\Semester::where('is_active', true)->value('id');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SetorHafalan::query()->where('semester_id', $this->getSemester())->where('siswa_id', auth()->user()->siswa->id)
            )
            ->columns([
                TextColumn::make('surat'),
                TextColumn::make('ayat'),
                TextColumn::make('nilai')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Mumtaz' => 'success',
                    'Jayyid Jiddan' => 'success',
                    'Jayyid' => 'info',
                    'Maqbul' => 'warning',
                    'Naqis' => 'danger',
                }),
                TextColumn::make('user.name')->label('Penyimak'),
                TextColumn::make('created_at')->label('Waktu')
                ->toggleable()
                ->date('d M Y, H:i'),
                TextColumn::make('keterangan')
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                //
            ])
            ->filters([
               //
            ]);
    }
}
