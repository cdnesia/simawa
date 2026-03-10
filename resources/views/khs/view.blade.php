@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Kartu hasil studi</h6>
    <hr>
    <div class="card">
        <div class="card-header">
            <div class="table-responsive">
                <table class="table table-borderless table-sm mb-2" style="font-weight: bold;">
                    <tbody>
                        <tr>
                            <td style="width: 220px;">Nama Mahasiswa</td>
                            <td style="width: 10px;">:</td>
                            <td>{{ $metadata['nama_mahasiswa'] }}</td>
                        </tr>
                        <tr>
                            <td>Nomor Pokok Mahasiswa</td>
                            <td>:</td>
                            <td>{{ $metadata['npm'] }}</td>
                        </tr>
                        <tr>
                            <td>Program Studi</td>
                            <td>:</td>
                            <td>{{ $metadata['nama_program_studi'] }}</td>
                        </tr>
                        <tr>
                            <td>Kelas Perkuliahan</td>
                            <td>:</td>
                            <td>{{ $metadata['nama_kelas'] }}</td>
                        </tr>
                        <tr>
                            <td>Pembimbing Akademik</td>
                            <td>:</td>
                            <td>{{ $metadata['dosen_pa'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <h5>Kartu Hasil Studi</h5>
                @if ($krs['semester'] && $krs['tahun_akademik'])
                    <h5>Semester {{ $krs['semester'] }} - Tahun Akademik {{ $krs['tahun_akademik'] }}</h5>
                    <a href="{{ route('khs.print') }}?periode={{ $krs['tahun_akademik'] }}" target="_blank"
                        class="btn btn-sm btn-primary me-0"><i class="bx bx-printer mr-1"></i> Cetak</a>
                @endif

            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered krsTable" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" rowspan="2" style="width: 30px">No</th>
                            <th class="text-center align-middle" rowspan="2" style="width: 100px">Kode</th>
                            <th class="text-center align-middle" rowspan="2" style="width: 400px">Mata Kuliah</th>
                            <th class="text-center align-middle" rowspan="2" style="width: 50px">SKS</th>
                            <th class="text-center align-middle" colspan="3">Nilai</th>
                        </tr>
                        <tr>
                            <th class="text-center align-middle" style="width: 50px">Angka</th>
                            <th class="text-center align-middle" style="width: 50px">Huruf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($krs['krs'] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['kode_mata_kuliah'] }}</td>
                                <td>{{ $item['nama_mata_kuliah'] }}</td>
                                <td class="text-center">{{ $item['sks_matakuliah'] }}</td>
                                <td class="text-center">{{ $item['nilai_angka'] }}</td>
                                <td class="text-center">{{ $item['nilai_huruf'] }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <th colspan="6">
                                Jumlah SKS : {{ $krs['jumlah_sks'] ?? 0 }} <br>
                                IP Semester : {{ $krs['metadata']['ips'] ?? 0 }} <br>
                                IP Kumulatif : {{ $krs['metadata']['ipk'] ?? 0 }} <br>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <nav aria-label="Page navigation example mb-0">
                Pilih Semester :
                <ul class="pagination mb-0">
                    @foreach ($semester as $key => $item)
                        <li class="page-item {{ request('periode') == $item['tahun_akademik'] ? 'active' : '' }}"><a class="page-link"
                                href="{{ route('khs.index') }}?periode={{ $item['tahun_akademik'] }}">{{ $item['semester'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
@endsection
