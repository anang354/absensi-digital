const notifIn = document.getElementById("notif_in");
const notifOut = document.getElementById("notif_out");
let latitudeSekolah = "";
let longitudeSekolah = "";
let radiusAbsen = "";

$.ajax({
    type: 'GET',
    url: '/pengaturan',
    success: function(response) {
      latitudeSekolah = response[0];
      longitudeSekolah = response[1];
      radiusAbsen = response[2];
      var lokasi = document.getElementById('lokasi'); 
      if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        
      }

      function successCallback(position)
      {
        let posLatitude = position.coords.latitude;
        let posLongitude = position.coords.longitude;
        lokasi.value = posLatitude+ ',' +posLongitude;
        var map = L.map('map').setView([posLatitude, posLongitude], 17);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 25,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        var marker = L.marker([posLatitude, posLongitude]).addTo(map);
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

Webcam.set({
        height: 480,
        width: 640,
        image_format: 'jpeg',
        jpeg_quality: 80,
      });
      Webcam.attach('.webcam-reader');

      $('#take_absen').click(function(){
        Webcam.snap(function(uri) {
          image = uri;
        });
        let lokasi = $('#lokasi').val();
        $.ajax({
          type: 'POST',
          url: '/guru-scan/store',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          data: {
            "image": image,
            "lokasi": lokasi,
          },
          cache: false,
          success: function(respond) {
            var status = respond.split("|");
            if(status[0] === "success") {
              if(status[2] === 'in') {
                notifIn.play();
              } else {
                notifOut.play();
              }
              Swal.fire({
              title: 'Berhasil!',
              text: status[1],
              icon: 'success',
              confirmButtonText: 'Oke'
            })
            setTimeout("location.href='/admin'", 3000)
            } else {
              Swal.fire({
              title: 'Error!',
              text: status[1],
              icon: 'error',
              confirmButtonText: 'Oke'
            });
            }
          }
        });
      });


var span = document.getElementById('waktu');

function time() {
  var d = new Date();
  var s = d.getSeconds();
  var m = d.getMinutes();
  var h = d.getHours();
  span.textContent = 
    ("0" + h).substr(-2) + ":" + ("0" + m).substr(-2) + ":" + ("0" + s).substr(-2);
}

setInterval(time, 1000);