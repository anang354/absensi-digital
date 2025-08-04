<x-filament-panels::page>
 
        <div
            x-data="{}"
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('leaflet-css'))]"
        >
        <div
            x-data="{}"
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('absen-guru'))]"
        >
            <!-- ... -->
        </div>
        {{-- <div
            x-data="{}"
            x-load-js="[@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('jquery'))]"
        >
            <!-- ... -->
        </div>
        <div
            x-data="{}"
            x-load-js="[@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('leaflet-js'))]"
        >
            <!-- ... -->
        </div> --}}
        {{-- <div
            x-data="{}"
            x-load-js="[@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('webcam'))]"
        >
            <!-- ... -->
        </div> --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <div class="box-guru">       
<x-filament::section icon="heroicon-o-camera">
    <x-slot name="heading">
        Foto Selfie
    </x-slot>
    

    <div class="webcam">
        <div class="webcam-reader"></div>
        
        <div class="button-camera">
            @php
                $checking = \App\Models\AbsenGuru::where('guru_id', auth()->user()->guru->id)->where('tanggal_presensi', date('Y-m-d'))->get();
                $jamSaatIni = Carbon\Carbon::now()->format('H:i');
                $batasAbsenPulang = '13:00';
            @endphp
                @if($checking->count() > 0)
                    @if($checking[0]->checkin !== null && $checking[0]->checkout !== null)
                        <x-filament::badge icon="heroicon-m-sparkles" color="info">
                            Anda Sudah Absen Hari Ini
                        </x-filament::badge>
                    @elseif($jamSaatIni > $batasAbsenPulang)
                        <x-filament::button icon="heroicon-o-camera" color="danger" id="take_absen">
                        Absen Pulang
                        </x-filament::button>
                    @endif
                @else
                <x-filament::button icon="heroicon-o-camera" color="success" id="take_absen">
                    Absen Masuk
                </x-filament::button>
                @endif
        </div>

        <div class="data-absen">
            <p>{{ Carbon\Carbon::now()->format('l, d F Y') }}</p>
            <p id="waktu"></p>
        </div>
        <x-filament::fieldset>
            <x-slot name="label">
                Riwayat Absensi    
            </x-slot>
            
{{ $this->table }}
{{-- <div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Tanggal
                </th>
                <th scope="col" class="px-6 py-3">
                    Checkin
                </th>
                <th scope="col" class="px-6 py-3">
                    Checkout
                </th>
                <th scope="col" class="px-6 py-3">
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Selasa, 17 Juni 2025
                </th>
                <td class="px-6 py-4">
                    <x-filament::badge color="success">
                        08:37
                    </x-filament::badge>
                </td>
                <td class="px-6 py-4">
                    -
                </td>
                <td class="px-6 py-4">
                    Hadir
                </td>
            </tr>
        </tbody>
    </table>
</div> --}}

            {{-- Form fields --}}
        </x-filament::fieldset>


    </div>
</x-filament::section>
   <x-filament::section icon="heroicon-o-map-pin">
    <x-slot name="heading">
        Lokasimu
    </x-slot>    
        <input type="text" id="lokasi" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="latitude, longitude" readonly>

        <div class="mt-2">
            <div id="map" style="height: 300px; "></div>
        </div>
<audio id="notif_in">
    <source src="{{ asset('sounds/success_in.mp3') }}" type="audio/mpeg">
</audio>
<audio id="notif_out">
    <source src="{{ asset('sounds/success_out.mp3') }}" type="audio/mpeg">
</audio>
    {{-- <div
        x-data="{}"
        x-load-js="[@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('absen-guru'))]"
    >
        <!-- ... -->
    </div> --}}
    <script src="{{ asset('js/absen-guru.js') }}" type="text/javascript"></script>
    
    </x-filament::section>
</div>

</x-filament-panels::page>