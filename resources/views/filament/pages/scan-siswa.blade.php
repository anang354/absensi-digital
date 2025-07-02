<x-filament-panels::page>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 <div class="box-guru">
<x-filament::section icon="heroicon-o-camera">
    <x-slot name="heading">
        Scan QR
    </x-slot>
    <div class="radio-button">
        <div class="flex w-full items-center ps-4 border border-gray-200 rounded-sm dark:border-gray-700">
            <input id="bordered-radio-1" type="radio" value="dhuha" name="tipe-absen" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="bordered-radio-1" class="w-full py-4 ps-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sholat Dhuha</label>
        </div>
        <div class="flex w-full items-center ps-4 border border-gray-200 rounded-sm dark:border-gray-700">
            <input id="bordered-radio-2" type="radio" value="ashar" name="tipe-absen" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="bordered-radio-2" class="w-full py-4 ps-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sholat Ashar</label>
        </div>
    </div>
<div id="reader" width="300px"></div>
</x-filament::section>
<x-filament::section icon="heroicon-o-user">
    <x-slot name="heading">
        Data Siswa
    </x-slot>
    <div style="display: flex; justify-content: center; flex-direction: column; align-items: center; gap: 20px;">
        <x-filament::avatar
                src="{{ asset('storage/images/user-default.png') }}"
                alt="Dan Harrin"
                id="img-siswa"
                size="w-1/2 h-1/2"
            />
            <h1 class="font-bold text-2xl" id="nama-siswa">{Nama Siswa}</h1>
            <h3 id="kelas-siswa">{Kelas Siswa}</h3>
            <div class="w-full">
                <x-filament::fieldset>
                <x-slot name="label">
                    Absensi Siswa Hari Ini oleh anda
                </x-slot>
                
                {{-- Form fields --}}
                <div class="filament-tables-wrapper" style="border-width:1px; border-color:#e5e7eb; border-radius: 0.5rem; overflow: hidden;">
            <table class="filament-tables w-full text-start divide-y divide-gray-200 dark:divide-white/5">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-2 font-medium text-sm text-gray-500 dark:text-gray-400">Tanggal</th>
                        <th class="px-4 py-2 font-medium text-sm text-gray-500 dark:text-gray-400">Nama</th>
                        <th class="px-4 py-2 font-medium text-sm text-gray-500 dark:text-gray-400">Waktu</th>
                        <th class="px-4 py-2 font-medium text-sm text-gray-500 dark:text-gray-400">Sholat</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="divide-y divide-gray-200 dark:divide-white/5 whitespace-nowrap">
                    {{-- Data akan diisi di sini oleh JavaScript --}}
                    <tr><td colspan="4" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
            </x-filament::fieldset>
            </div>
    </div>
</x-filament::section>
</div>
<script type="text/javascript">
let radio = $('input[name="tipe-absen"]');
let tipe_absen = '';
let waitingTimeout;
let isWaitingForResponse = false;
const imgSiswa = $('#img-siswa');
const namaSiswa = $('#nama-siswa');
const kelasSiswa = $('#kelas-siswa');

async function loadAttendanceHistory() {
    const $tableBody = $('#history-table-body');
    $tableBody.html('<tr><td colspan="4" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">Memuat data...</td></tr>'); // Loading state

    try {
        const data = await $.ajax({
            url: '/siswa-scan',
            method: 'GET',
            dataType: 'json' // Harapkan respon JSON
        });

        if (data.length === 0) {
            $tableBody.html('<tr><td colspan="4" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">Belum ada riwayat absensi.</td></tr>');
            console.log("GAGAL")
            return;
        }
        console.log(data);
        let rowsHtml = '';
        $.each(data, function(index, record) {
            rowsHtml += `
                <tr class="hover:bg-gray-50 text-sm dark:hover:bg-gray-800">
                    <td class="px-4 py-2 text-gray-950 dark:text-white">${record.tanggal}</td>
                    <td class="px-4 py-2 text-gray-950 dark:text-white">${record.nama}</td>
                    <td class="px-4 py-2 text-gray-950 dark:text-white">${record.waktu}</td>
                    <td class="px-4 py-2 text-gray-950 dark:text-white">${record.tipe}</td>
                </tr>
            `;
        });
        $tableBody.html(rowsHtml);

    } catch (jqXHR) {
        console.error("Gagal memuat riwayat absensi:", jqXHR);
        $tableBody.html('<tr><td colspan="4" class="px-4 py-2 text-center text-danger-500 dark:text-danger-400">Gagal memuat data absensi.</td></tr>');
    }
}

loadAttendanceHistory();
radio.click(function() {
    tipe_absen = $('input[name="tipe-absen"]:checked').val();
})
function onScanSuccess(decodedText, decodedResult) {
  // handle the scanned code as you like, for example:
  //console.log(`Code matched = ${decodedText}`, decodedResult);
   if (isWaitingForResponse) {
        console.log("Still waiting for the previous scan...");
        return;
      }
  clearTimeout(waitingTimeout);
  if(tipe_absen === '') {
    Swal.fire({
        title: 'Warning!',
        text: 'Pilih Tipe Absen Dulu!',
        icon: 'error',
        confirmButtonText: 'Oke'
    });
    isWaitingForResponse = true;
  } else {
    $.ajax({
          type: 'POST',
          url: '/siswa-scan/store',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          data: {
            "nisn": decodedText,
            "tipe": tipe_absen,
          },
          cache: false,
          success: function(respond) {
                var status = respond.split("|");
                if(status[0] === "success") {
                    imgSiswa.attr("src", status[2]);
                    namaSiswa.text(status[3]);
                    kelasSiswa.text(status[4]);
                    loadAttendanceHistory();
                    Swal.fire({
                        title: status[0],
                        text: status[1],
                        icon: status[0],
                        timer: 2000,
                        confirmButtonText: 'Oke'
                    });
                } else {
                    Swal.fire({
                        title: status[0],
                        text: status[1],
                        icon: status[0],
                        confirmButtonText: 'Oke'
                    });
                }
                
          }
    });   
        
        isWaitingForResponse = true;
  }
  waitingTimeout = setTimeout(() => {
    isWaitingForResponse = false;
  }, 4000);
  
}

function onScanFailure(error) {
  // handle scan failure, usually better to ignore and keep scanning.
  // for example:
//   console.warn(`Code scan error = ${error}`);
}

let html5QrcodeScanner = new Html5QrcodeScanner(
  "reader",
  { fps: 10, qrbox: {width: 300, height: 300} },
  /* verbose= */ false);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
</x-filament-panels::page>
