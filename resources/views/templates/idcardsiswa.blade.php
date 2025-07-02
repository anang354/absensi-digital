<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card Potrait</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        .page-break {
            page-break-after: always;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
        }

        table.page {
            width: 100%;
            border-collapse: collapse;
        }

        td.idcell {
            width: 50%;
            height: 9.5cm;
            padding: 0.2cm;
            vertical-align: top;
        }

        .idcard {
            width: 6.5cm;
            height: 9cm;
            border: 2px solid #333;
            border-radius: 10px;
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }

        .photo {
            width: 2.5cm;
            height: 2.5cm;
            padding: 5px;
            background-color: #2c84e9;
            margin: 5px auto;
            border-radius: 100%;
        }
        .photo img {
             width: 2.5cm;
            height: 2.5cm;
            border-radius: 100%;
        }

        .biodata {
            font-size: 14px;
            font-weight: bold;
        }
        .biodata h2 {
            text-transform: uppercase;
            font-size: 11pt;
            background: #065a9e;
            border-radius: 5px;
            color: white;
            margin: 10px auto;
            padding: 5px 0;
        }
        .biodata p {
            font-size: 9pt;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .page-break {
            page-break-after: always;
        }
        .header {
            width: 100%;
            display: block;
        }
        .logo {
            display: block;
            margin: 5px;
        }
        .kop {
            width: auto;
            text-align: center;
        }
        .kop h4 {
            font-size: 9pt;
            padding: 0;
            margin: 0;
            text-transform: uppercase;
        }
        .kop h2 {
            font-size: 12pt;
            padding: 0;
            margin: 0;
        }
        .left-block {
            width: 10px;
            height: 150px;
            background: #2c84e9;
            border-radius: 10px;
            position: absolute;
            left: 0;
            top: 30%;
        }
        .right-block {
            width: 10px;
            height: 150px;
            background: #2c84e9;
            border-radius: 10px;
            position: absolute;
            right: 0;
            top: 30%;
        }
       
    </style>
</head>
<body>
    @foreach(collect($data)->chunk(count($data)) as $chunk)
    <table class="page">
        @for($i = 0; $i < ceil(count($data) / 4); $i++) {{-- baris --}}
        <tr>
            @for($j = 0; $j < 4; $j++) {{-- kolom --}}
                @php
                    $index = ($i * 4) + $j;
                    $item = $chunk[$index] ?? null;
                @endphp
                <td class="idcell">
                    @if($item)
                    
                    @php
                        $empImage = null;
                        $path = public_path().'/storage/' . $item['foto'];
                        

                        if (!empty($item['foto']) && file_exists($path) && is_file($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $dt = file_get_contents($path);
                            $empImage = 'data:image/' . $type . ';base64,' . base64_encode($dt);
                        }
                    @endphp
                    <div class="idcard">
                        <div class="left-block"></div>
                        <div class="right-block"></div>
                        <div class="header">
                            <div class="logo">
                                <img src="{{$logo}}" alt="" width="50">
                            </div>
                            <div class="kop">
                                <h4>{{ $namaSekolah }}</h4>
                                <h2>KARTU PELAJAR</h2>
                            </div>
                        </div>
                        <div class="photo">
                            <img src="{{ $empImage }}" alt="Foto">
                        </div>
                        <div class="biodata">
                            {{-- <h2>{{ $item['nama'] }}</h2> --}}
                            <p>{{$item['nama']}}</p>
                            <p>{{$item['kelas']}}</p>
                            <img class="qr-code" width="65px" src="{{ $item['qr_code'] }}" alt="QR Code NISN {{ $item['nisn'] }}">
                        </div>
                    </div>
                    @endif
                </td>
            @endfor
        </tr>
        @endfor
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
