<x-filament-panels::page>
        Daftar Guru Belum Absen Hari ini ({{ \Carbon\Carbon::today()->format('d F Y') }})
{{$this->table}}
</x-filament-panels::page>
