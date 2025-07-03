<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Guru;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Imports\GurusImport;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GuruResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Michaeld555\FilamentCroppie\Components\Croppie;
use Filament\Tables\Actions\Action; // <<< Import Action
use App\Filament\Resources\GuruResource\RelationManagers;
use Filament\Forms\Components\FileUpload; // <<< Import FileUpload
use Filament\Notifications\Notification; // <<< Import Notification
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage; 
use Filament\Forms\Components\Actions\Action as FormAction; // <<< Import ini untuk Action di dalam Form
use Illuminate\Support\Facades\URL; // <<< Import ini
use Filament\Forms\Components\Actions; // <<< Import ini

class GuruResource extends Resource
{
    protected static ?string $model = Guru::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Management Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\Section::make('Personal Data')->schema([
                        Forms\Components\TextInput::make('nama')->required()
                        ->prefixIcon('heroicon-o-user-circle'),                
                        Forms\Components\Radio::make('jenis_kelamin')
                        ->options([
                            'pria' => 'Pria',
                            'wanita' => 'Wanita',
                        ]),
                        Forms\Components\TextInput::make('nomor_handphone')->required()->numeric()
                        ->prefixIcon('heroicon-o-phone'),
                        Forms\Components\TextInput::make('tempat_lahir')
                        ->prefixIcon('heroicon-o-map-pin'),
                        Forms\Components\DatePicker::make('tanggal_lahir'),
                        Forms\Components\Textarea::make('alamat'),
                        Forms\Components\TextInput::make('nik')->numeric(),
                        Forms\Components\TextInput::make('nip')->label('NIP/NUPTK')->numeric(),
                    ])->columnSpan(4)->columns(2),
                Forms\Components\Section::make('User')->schema([
                    Forms\Components\TextInput::make('username')->required()
                    ->unique(ignoreRecord: true, table: 'users')
                    ->validationMessages([
                        'unique' => 'Username sudah terdaftar, coba yang lainnya.',
                    ])
                    ->prefixIcon('heroicon-o-user')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Guru $record): void {
                        // Isi username saat mengedit dari model User yang berelasi
                        if ($record && $record->user) {
                            $component->state($record->user->username);
                        }
                    }),
                    Forms\Components\TextInput::make('password')->password()
                    ->prefixIcon('heroicon-o-cog-8-tooth')
                    ->required(fn (string $operation): bool => $operation === 'create') // Wajib saat membuat, tidak wajib saat mengedit
                        ->autocomplete('new-password') // Membantu browser tidak mengisi otomatis
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)) // Hash password
                        ->dehydrated(fn (?string $state): bool => filled($state)) // Hanya simpan jika ada isinya
                        ->visible(fn (string $operation): bool => $operation === 'create'), // Sembunyikan saat mengedit
                    
                    //Forms\Components\FileUpload::make('foto')->disk('public'),
                    Croppie::make('foto')->disk('public')->directory('guru')
                        ->modalDescription('Silahkan potong gambar sesuai kebutuhan, usahakan wajah berada ditengah')
                        ->viewportType('square')
                        ->viewportHeight(400)
                        ->viewportWidth(400)
                        ->preserveFilenames(false)
                        ->columnSpanFull(),
                ])->columnSpan(2),
        ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')->circular(),
                TextColumn::make('nip')->copyable()->searchable()
                ->copyMessage('Nip copied'),
                TextColumn::make('nama')->searchable(),
                TextColumn::make('jenis_kelamin'),
                TextColumn::make('nomor_handphone')->copyable()
                ->copyMessage('Nomor Handphone copied'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('importGurus')
                    ->color('success')
                    ->label('Import Data')
                    ->modalHeading('Import Data Guru dari Excel')
                    ->modalDescription('Download file contoh import dibawah ini untuk melakukan import data menggunakan excel')
                    ->form([
                        // <<< TAMBAHKAN BAGIAN INI
                        Actions::make([
                            FormAction::make('download_template')
                                ->label('Download Contoh Excel')
                                ->icon('heroicon-o-document-arrow-down')
                                ->url(URL::asset('excel_templates/guru_import.xlsx')) // Sesuaikan path file
                                ->color('info')
                                ->openUrlInNewTab(),
                        ])->alignStart(), 
                        FileUpload::make('file')
                            ->label('Upload File Excel')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                    ])
                    ->action(function (array $data) {
                        // $data['file'] sekarang adalah string path relatif ke disk 'public'
                        $relativePath = $data['file'];

                        // Dapatkan path absolut dari file di disk 'public'
                        $absoluteFilePath = Storage::disk('public')->path($relativePath);

                        // Anda bisa dd($absoluteFilePath) di sini untuk memastikan path-nya benar
                        // dd($absoluteFilePath);

                        if (!file_exists($absoluteFilePath)) {
                            Notification::make()
                                ->title('Import Gagal!')
                                ->body('File yang diunggah tidak ditemukan di lokasi penyimpanan. Silakan coba lagi.')
                                ->danger()
                                ->persistent()
                                ->send();
                            return; // Hentikan eksekusi
                        }

                        try {
                            // Gunakan path absolut untuk import
                            Excel::import(new \App\Imports\GurusImport, $absoluteFilePath);

                            Notification::make()
                                ->title('Import berhasil!')
                                ->body('Data guru dari Excel berhasil diimport.')
                                ->success()
                                ->send();
                        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                            $failures = $e->failures();
                            $errorMessages = [];
                            foreach ($failures as $failure) {
                                $errorMessages[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
                            }
                            Notification::make()
                                ->title('Import gagal!')
                                ->body('Ada kesalahan dalam file Excel Anda: <br>' . implode('<br>', $errorMessages))
                                ->danger()
                                ->persistent()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import gagal!')
                                ->body('Terjadi kesalahan saat mengimport data: ' . $e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        } finally {
                            // Hapus file dari storage setelah diproses
                            // Gunakan $relativePath karena kita menghapus dari disk 'public'
                            Storage::disk('public')->delete($relativePath);
                        }
                    })
                    ->icon('heroicon-o-arrow-up-on-square'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AbsenGurusRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGurus::route('/'),
            'create' => Pages\CreateGuru::route('/create'),
            'edit' => Pages\EditGuru::route('/{record}/edit'),
        ];
    }

}
