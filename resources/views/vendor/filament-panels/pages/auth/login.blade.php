<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif
        @php
            if(\App\Models\Pengaturan::first() && \App\Models\Pengaturan::first()->logo_sekolah) {
                $logoPath = asset('storage/' . \App\Models\Pengaturan::first()->logo_sekolah);
            } else {
                $logoPath = asset('css/logo_default.png'); // Ganti dengan path ke file default Anda
            }
        @endphp
    <div class="flex justify-center">
        <img src="{{ $logoPath }}" width="120px" alt="Logo Sekolah">
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}
    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
