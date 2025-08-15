<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save"> 
        {{ $this->form }}
 
        <x-filament-panels::form.actions 
            :actions="$this->getFormActions()"
        /> 
    </x-filament-panels::form>
    <x-filament::section
    icon="heroicon-o-book-open"
    icon-color="info"
>
    <x-slot name="heading">
        Daftar Setor Hafalan oleh anda hari ini
    </x-slot>
    {{ $this->table }}
</x-filament::section>
</x-filament-panels::page>
