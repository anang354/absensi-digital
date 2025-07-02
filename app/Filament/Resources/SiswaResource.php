<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Siswa;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Imports\SiswaImport;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Actions;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SiswaResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Michaeld555\FilamentCroppie\Components\Croppie;
use Illuminate\Support\Facades\URL; // <<< Import ini
use App\Filament\Actions\Siswas\IdCardSiswaBulkAction;
use App\Filament\Resources\SiswaResource\RelationManagers;
use Filament\Forms\Components\Actions\Action as FormAction;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Management Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Data')->schema([
                    Forms\Components\TextInput::make('nama')->required()
                    ->prefixIcon('heroicon-o-user-circle'),                
                        Forms\Components\Radio::make('jenis_kelamin')
                        ->options(Siswa::GENDERS)->required(),
                        Forms\Components\TextInput::make('nisn')->label('NISN/NIS')
                        ->required()->numeric(),
                        Forms\Components\TextInput::make('nomor_hp')->required()->numeric()
                        ->prefixIcon('heroicon-o-phone'),
                        Forms\Components\Textarea::make('alamat'),
                        Forms\Components\TextInput::make('wali')->label('Nama Wali Murid'),
                    ])->columnSpan(4)->columns(2),
                Forms\Components\Section::make('User')->schema([
                    Forms\Components\Select::make('kelas_id')->label('Kelas')
                    ->options(\App\Models\Kelas::all()->pluck('nama_kelas', 'id')),
                    Forms\Components\TextInput::make('username')->required()
                    ->unique(ignoreRecord: true, table: 'users')
                    ->validationMessages([
                        'unique' => 'Username sudah terdaftar, coba yang lainnya.',
                    ])
                    ->prefixIcon('heroicon-o-user')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Siswa $record): void {
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
                    Croppie::make('foto')->disk('public')->directory('siswa')
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
                TextColumn::make('nisn')->searchable()->copyable()
                ->copyMessage('Nip copied'),
                TextColumn::make('nama')->searchable(),
                TextColumn::make('kelas.nama_kelas'),
                TextColumn::make('jenis_kelamin'),
                TextColumn::make('nomor_hp'),
                TextColumn::make('wali')->toggleable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('importSiswa')
                ->color('success')
                    ->label('Import Data')
                    ->modalHeading('Import Data Siswa dari Excel')
                    ->modalDescription('Download file contoh import dibawah ini untuk melakukan import data menggunakan excel')
                    ->form([
                        // <<< TAMBAHKAN BAGIAN INI
                        Actions::make([
                            FormAction::make('download_template')
                                ->label('Download Contoh Excel')
                                ->icon('heroicon-o-document-arrow-down')
                                ->url(URL::asset('excel_templates/siswa_import.xlsx')) // Sesuaikan path file
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
                            Excel::import(new \App\Imports\SiswaImport, $absoluteFilePath);

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
                    IdCardSiswaBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswas::route('/'),
            'create' => Pages\CreateSiswa::route('/create'),
            'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }
}
