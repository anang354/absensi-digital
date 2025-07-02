<x-filament-panels::page>
    <p>Siswa yang belum absen hari ini : {{ date('l, d M Y') }}</p>
    {{ $this->table }}
</x-filament-panels::page>
