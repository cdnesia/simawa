<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $filename ?? "Trankrip Nilai" }}</title>
    </head>
    <style>
        table td,
        table th {
            vertical-align: top;
        }
    </style>
    <style>
        .tabel-wrapper {
            display: flex;
            justify-content: space-between;
            /* beri jarak otomatis */
            gap: 20px;
            /* jarak antar tabel */
        }

        .tabel-wrapper table {
            width: 48%;
            /* dua tabel muat di 1 baris */
            border-collapse: collapse;
        }

        .tabel-wrapper td {
            vertical-align: top;
            padding: 4px 6px;
        }
    </style>

    <body>
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:50%; vertical-align:top;">
                    <table>
                        <tr>
                            <td>Nama <br><span style="font-style:italic">Name</span></td>
                            <td>:</td>
                            <td>{{ $mahasiswa->nama_mahasiswa }}</td>
                        </tr>
                        <tr>
                            <td>Tempat, Tanggal Lahir<br><span style="font-style:italic">Place / Date of Birth</span>
                            </td>
                            <td>:</td>
                            <td>{{ $mahasiswa->tempat_lahir . "," . $mahasiswa->tanggal_lahir }}</td>
                        </tr>
                        <tr>
                            <td>Nomor Pokok Mahasiswa <br><span style="font-style:italic">Student ID</span></td>
                            <td>:</td>
                            <td>{{ $mahasiswa->npm }}</td>
                        </tr>
                        <tr>
                            <td>No. SK Akreditasi Program Studi <br><span style="font-style:italic">Student ID</span></td>
                            <td>:</td>
                            <td>{{ $mahasiswa->npm }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%; vertical-align:top;">
                    <table>
                        <tr>
                            <td>PISN <br><span style="font-style:italic">Name</span></td>
                            <td>:</td>
                            <td>{{ $mahasiswa->nama_mahasiswa }}</td>
                        </tr>
                        <tr>
                            <td>Jenjang Pendidikan<br><span style="font-style:italic">Degree</span>
                            </td>
                            <td>:</td>
                            <td>{{ $mahasiswa->tempat_lahir . "," . $mahasiswa->tanggal_lahir }}</td>
                        </tr>
                        <tr>
                            <td>Program Studi <br><span style="font-style:italic">Study Program</span></td>
                            <td>:</td>
                            <td>{{ $mahasiswa->npm }}</td>
                        </tr>
                        <tr>
                            <td>Nomor Seri Ijazah <br><span style="font-style:italic">Certificate Serial Number</span></td>
                            <td>:</td>
                            <td>{{ $mahasiswa->npm }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        @php
            $no = 1;
        @endphp
        <table>
            @foreach ($transkrip as $a => $item)
                @foreach ($item["matakuliah"] as $b)
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $b["kode_mk"] }}</td>
                        <td>{{ $b["nama_mk"] }}</td>
                        <td>{{ $b["sks"] }}</td>
                        <td>{{ $b["nilai_angka"] }}</td>
                        <td>{{ $b["nilai_bobot"] }}</td>
                        <td>{{ $b["nilai_huruf"] }}</td>
                    </tr>
                    @php
                        $no++;
                    @endphp
                @endforeach
            @endforeach
        </table>
    </body>

</html>
