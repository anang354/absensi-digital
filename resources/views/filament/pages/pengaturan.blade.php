<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save"> 
        {{ $this->form }}
 
        <x-filament-panels::form.actions 
            :actions="$this->getFormActions()"
        /> 
    </x-filament-panels::form>
    <x-filament::section icon="heroicon-o-map">
        <x-slot name="heading">
            Lokasi Sekolah
        </x-slot>
        <p class="text-danger">Refresh halaman setelah melakukan perubahan data untuk melihat hasil perubahannya</p>
        <div class="mt-2">
            <div id="map" style="height: 300px; "></div>
        </div>
    </x-filament::section>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" type="text/javascript"></script>
    <script type="text/javascript">
        $.ajax({
    type: 'GET',
    url: '/pengaturan',
    success: function(response) {
      latitudeSekolah = response[0];
      longitudeSekolah = response[1];
      radiusAbsen = response[2];
      //var lokasi = document.getElementById('lokasi'); 
      if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        
      }

      function successCallback(position)
      {
        let posLatitude = position.coords.latitude;
        let posLongitude = position.coords.longitude;
        //lokasi.value = posLatitude+ ',' +posLongitude;
        var map = L.map('map').setView([posLatitude, posLongitude], 17);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 25,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        var marker = L.marker([latitudeSekolah, longitudeSekolah]).addTo(map);
        var circle = L.circle([latitudeSekolah, longitudeSekolah], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: radiusAbsen
        }).addTo(map);
      }
      function errorCallback()
      {

      }

    }
});
    </script>
</x-filament-panels::page>
