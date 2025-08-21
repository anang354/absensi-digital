<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class UbahPassword extends Page implements HasForms
{
    
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.ubah-password';

    protected static ?int $navigationSort = 999;

    public ?string $current_password, $new_password, $new_password_confirmation;

    public static function canAccess(): bool
    {
        return auth()->user()->level !== 'siswa';
    }


    public function form(Form $form): Form
    {   
        return $form 
        ->schema([
            TextInput::make('current_password')
                ->label('Password Lama')
                ->password()
                ->required(),

            TextInput::make('new_password')
                ->label('Password Baru')
                ->password()
                ->required()
                ->minLength(4),

            TextInput::make('new_password_confirmation')
                ->label('Konfirmasi Password Baru')
                ->password()
                ->same('new_password')
                ->required(),
        ]);
    }

    protected function getFormActions(): array 
    {
        return [
            Action::make('save')->submit('save')->label('Ubah Password'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (!Hash::check($data['current_password'], auth()->user()->password)) {
            Notification::make()
                ->title('Password lama salah.')
                ->danger()
                ->send();
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($data['new_password']),
        ]);

        Notification::make()
            ->title('Password berhasil diubah.')
            ->success()
            ->send();

        $this->form->fill([]); // reset form
        $this->current_password = null;
        $this->new_password = null;
        $this->new_password_confirmation = null;
    }


}
