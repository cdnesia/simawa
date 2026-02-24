@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Riwayat Pembayaran</h6>
    <hr>
    <div class="card">
        <div class="card-header">
            <div class="table-responsive">
                <table class="table table-borderless table-sm mb-2">
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
            <button id="hubungi_pa" class="btn btn-warning btn-sm me-1"><i class="bx bx-send"></i>Hubungi PA</button>
        </div>
    </div>
    {{-- @dd($krs) --}}
    @foreach ($krs as $key => $value)
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h6 class="mb-0">Tahun Akademik {{ $key }} - Semester {{ $value['semester'] }}</h6>
                <div class="ms-auto">
                    <a href="" class="btn btn-sm btn-primary me-0"><i class="bx bx-printer mr-1"></i> Cetak</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered krsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 30px" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Mata Kuliah</th>
                                <th colspan="3" class="text-center">Nilai</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="width: 75px">Nilai Angka</th>
                                <th class="text-center" style="width: 75px">Nilai Huruf</th>
                                <th class="text-center" style="width: 75px">Nilai Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($value['krs'] as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['kode_mata_kuliah'] }}<br>
                                        {{ $item['nama_mata_kuliah'] }}</td>
                                    <td>{{ $item['nilai_angka'] }}</td>
                                    <td>{{ $item['nilai_huruf'] }}</td>
                                    <td>{{ $item['nilai_bobot'] }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="table-responsive">
                    <table class="table table-borderless table-sm mb-2">
                        <thead>
                            <tr>
                                <th style="width: 100px;">IP Semester</th>
                                <th style="width: 10px;">:</th>
                                <th>{{ $value['metadata']['ips'] }}</th>
                            </tr>
                            <tr>
                                <th style="width: 100px;">IP Kumulatif</th>
                                <th style="width: 10px;">:</th>
                                <th>{{ $value['metadata']['ipk'] }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
@push('css')
@endpush
@push('js')
@endpush
