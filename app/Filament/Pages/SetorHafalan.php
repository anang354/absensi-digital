<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder; // Untuk query Builder
use Livewire\Attributes\Reactive; // Import ini untuk Livewire 3

class SetorHafalan extends Page implements HasForms, HasTable
{

    use InteractsWithForms, InteractsWithTable;

    public ?array $siswaData = null;
    public $siswa_id, $surat, $ayat, $nilai, $keterangan;
    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.setor-hafalan';

    protected static ?string $navigationGroup = 'Hafalan Siswa';

    public static function canAccess(): bool
    {
        return auth()->user()->level !== 'siswa';
    }

    public function table(Table $table): Table
    {
        $today = \Carbon\Carbon::today();
        $yesterday = \Carbon\Carbon::yesterday();
        return $table
        ->query(\App\Models\SetorHafalan::query()->where('user_id', auth()->user()->id)->whereBetween('created_at', [$yesterday, $today->endOfDay()])->orderByDesc('created_at'))
        ->columns([
            TextColumn::make('siswa.nama')->searchable(),
                TextColumn::make('siswa.kelas.nama_kelas'),
                TextColumn::make('surat')->limit(50),
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
                TextColumn::make('created_at')->label('Waktu')
                ->toggleable()
                ->date('d M Y, H:i'),
                TextColumn::make('keterangan')
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                \Filament\Tables\Actions\DeleteAction::make()
                    ->visible(fn(\App\Models\SetorHafalan $record) => auth()->user()->id === $record->user_id),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('')
            ->schema([
            Section::make('')
                ->schema([
                    Select::make('siswa_id')
                    ->options([
                        \App\Models\Siswa::whereHas('user', function ($query) {
                            $query->where('is_active', true);
                        })->pluck('nama', 'id')->toArray()
                    ])
                    ->required()
                    ->searchable()
                    ->columnSpan(2)
                    ->live() // KUNCI: Membuat field ini bereaksi secara real-time
                            ->afterStateUpdated(function (?int $state) {
                                // Ketika siswa_id berubah, update properti siswaData
                                $this->siswaData = null; // Reset dulu

                                if ($state) {
                                    $siswa = \App\Models\Siswa::find($state);
                                    if ($siswa) {
                                        $this->siswaData = [
                                            'kelas' => $siswa->kelas->nama_kelas ?? 'N/A', // Asumsi ada relasi kelas()
                                            'nisn' => $siswa->nisn ?? 'N/A', // Asumsi ada kolom nisn
                                            'foto_siswa' => $siswa->foto, // Asumsi ada kolom foto_siswa
                                            'nama_siswa' => $siswa->nama_siswa // Untuk ditampilkan
                                        ];
                                    }
                                }
                            }),
                    Placeholder::make('kelas')
                            ->label('Kelas')
                            ->content(fn () => $this->siswaData['kelas'] ?? 'Belum dipilih')
                            ->visible(fn () => !is_null($this->siswaData))
                            ->columnSpan(1),
                    Placeholder::make('nisn')
                            ->label('NISN')
                            ->content(fn () => $this->siswaData['nisn'] ?? 'Belum dipilih')
                            ->visible(fn () => !is_null($this->siswaData))
                            ->columnSpan(1),
                    Placeholder::make('foto')
                            ->label('Foto Siswa')
                            ->content(
                                fn () => $this->siswaData['nisn'] ? new \Illuminate\Support\HtmlString("<img src='" . asset('storage/' . $this->siswaData['foto_siswa']) . "' width='150' style='border-radius:100%'>") : 'Belum dipilih'
                                )
                            ->visible(fn () => !is_null($this->siswaData))
                            ->columnSpan(2),
                ])->columns(2)->columnSpan(1),
            Section::make('')
                ->schema([
                    Select::make('surat')->required()
                        ->options([
                            \App\Models\SetorHafalan::SURAT_ALQURAN
                        ])
                        ->searchable()
                        ->multiple(),
                    TextInput::make('ayat'),
                    Select::make('nilai')->options([
                        \App\Models\SetorHafalan::NILAI_HAFALAN
                    ])->required(),
                    TextInput::make('keterangan'),
                ])->columnSpan(1),
            ])->columns(2)
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->submit('save')->label('Simpan Hafalan')
            ->icon('heroicon-o-plus'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $semesterId = \App\Models\Semester::where('is_active', true)->value('id');
        $stringSurat = implode(', ', $data['surat']);
        try {
            \App\Models\SetorHafalan::create([
            'siswa_id' => $data['siswa_id'],
            'semester_id' => $semesterId,
            'user_id' => auth()->user()->id,
            'surat' => $stringSurat,
            'ayat' => $data['ayat'],
            'nilai' => $data['nilai'],
            'keterangan' => $data['keterangan'],
            ]);
            Notification::make()
            ->success()
            ->title('Saved successfully')
            ->send();
            $this->form->fill();
            $this->siswaData = null;
        } catch (\Exception $e) {
            Notification::make()
            ->danger()
            ->title($e)
            ->send();
        }
    }
}
