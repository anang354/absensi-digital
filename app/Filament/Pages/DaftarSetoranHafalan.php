<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\SetorHafalan;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class DaftarSetoranHafalan extends Page implements HasTable
{

    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.daftar-setoran-hafalan';

    protected static ?string $navigationGroup = 'Hafalan Siswa';

    protected static ?int $navigationSort = 2;

    protected static ?string $model = SetorHafalan::class;

    public static function canAccess(): bool
    {
        return auth()->user()->level !== 'siswa';
    }

    protected function getSemester()
    {
        return \App\Models\Semester::where('is_active', true)->value('id');
    }

    public function table(Table $table): Table
    {
        if(auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin')
        {
            $queryHafalan = SetorHafalan::query()->where('semester_id', $this->getSemester());
        } else {
            $queryHafalan = SetorHafalan::query()
            ->where('semester_id', $this->getSemester())
            ->whereHas('siswa.kelas', function (Builder $query) {
                $query->where('jenjang', auth()->user()->guru->jenjang);
            });
        }
        return $table
            ->query(
                $queryHafalan
            )
            ->columns([
                TextColumn::make('siswa.nama')->searchable(),
                TextColumn::make('siswa.kelas.nama_kelas'),
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
                Action::make('edit')
                    ->form([
                        TextInput::make('surat'),
                        TextInput::make('ayat'),
                        Select::make('nilai')
                        ->options([
                           SetorHafalan::NILAI_HAFALAN
                        ]),
                    ])
                    ->fillForm(function (SetorHafalan $record) {
                        return $record->only(['surat', 'ayat', 'nilai']);
                    })
                    ->action(function (array $data, SetorHafalan $record) {
                        $record->update($data);
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\DeleteAction::make()
                    ->visible(fn(SetorHafalan $record) => auth()->user()->id === $record->user_id),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make()
                    ->visible(
                        function() {
                            return auth()->user()->level === 'admin' || auth()->user()->level === 'superadmin';
                        }
                    ),
                ]),
            ])
            ->filters([
                SelectFilter::make('kelas')
                    ->relationship('siswa.kelas', 'nama_kelas') // Ini adalah kuncinya!
                    ->label('Filter Berdasarkan Kelas')
                    ->placeholder('Pilih Kelas'),
            ]);
    }
}
