<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Michaeld555\FilamentCroppie\Components\Croppie;

class Pengaturan extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = []; 

    public ?\App\Models\Pengaturan $record = null;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.pengaturan';

    protected ?string $subheading = 'Form untuk pengaturan website';

    public function mount(): void 
    {
        $this->record = \App\Models\Pengaturan::firstOrCreate([]);

        // Isi form dengan data dari record
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                    Section::make('')->schema([
                        TextInput::make('nama_sekolah')->required(),                
                        TextInput::make('token_whatsapp'),
                        Textarea::make('alamat_sekolah')->required()->columnSpanFull(),
                        TextInput::make('latitude')->required(),
                        TextInput::make('longitude')->required(),
                        TextInput::make('radius')->numeric()->suffix('meters')->required(),
                    ])->columnSpan(4)->columns(2),
              Section::make('')->schema([     
                    //Forms\Components\FileUpload::make('foto')->disk('public'),
                    Croppie::make('logo_sekolah')->disk('public')->directory('images')
                        ->modalDescription('Posisikan logo berada di tengah')
                        ->viewportType('square')
                        ->viewportHeight(400)
                        ->viewportWidth(400)
                        ->preserveFilenames(false)
                        ->columnSpanFull(),
                ])->columnSpan(2),
        ])->columns(6)->statePath('data')->model($this->record);;
    }

    protected function getFormActions(): array 
    {
        return [
            Action::make('save')->submit('save')
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
 
            $this->record->update($data);
        } catch (Halt $exception) {
            return;
        }
 
        Notification::make() 
            ->success()
            ->title('Berasil menyimpan data')
            ->send(); 
    }
}
