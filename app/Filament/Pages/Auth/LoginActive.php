<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BasePage;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Validation\ValidationException;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\Component;

class LoginActive extends BasePage
{
    protected function getForms() :array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                ->schema([
                    $this->getLoginFormComponent(),
                    $this->getPasswordFormComponent(),
                    $this->getRememberFormComponent()
                ])
                ->statePath('data'),
            )
        ];
    }
    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
        ->label(__('Username / Email'))
        ->required()
        ->autocomplete()
        ->autofocus()
        ->extraInputAttributes(['tabindex' > 1]);
    }
    protected function getCredentialsFromFormData(array $data): array
    {
        $loginField = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $loginField => $data['login'],
            'password' => $data['password'],
        ];
    }
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        } else if (
            $user->is_active === 0
        ) {
            Filament::auth()->logout();
            throw ValidationException::withMessages([
                'data.login' => 'Akun anda tidak aktif',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
     protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}