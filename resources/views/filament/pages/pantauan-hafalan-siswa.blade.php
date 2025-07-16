<x-filament-panels::page>
    <p>Berikut adalah hasil setoran {{ auth()->user()->siswa->nama }} pada semester ini </p>
    {{ $this->table }}
</x-filament-panels::page>
