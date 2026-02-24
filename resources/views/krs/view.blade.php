@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Kartu rencana studi</h6>
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

            <a href="{{ route('krs.create') }}" class="btn btn-success btn-sm me-1"><i class="bx bx-file"></i>Kontrak Mata
                Kuliah</a>
            <button id="hubungi_pa" class="btn btn-warning btn-sm me-1"><i class="bx bx-send"></i>Hubungi PA</button>
        </div>
    </div>
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
                                <th style="width: 30px">No</th>
                                <th style="width: 200px">Hari/Ruang/Jam</th>
                                <th style="width: 400px">Mata Kuliah</th>
                                <th>Dosen Pengampu</th>
                                <th>Kelompok</th>
                                <th>Persetujuan PA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($value['krs'] as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['nama_hari'] }}/{{ $item['ruang_id'] }}<br>
                                        {{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}</td>
                                    <td>{{ $item['kode_mata_kuliah'] }}<br>
                                        {{ $item['nama_mata_kuliah'] }}</td>
                                    <td>{{ $item['dosen_id'] }}</td>
                                    <td>{{ $item['kelompok'] }}</td>
                                    <td>{{ $item['persetujuan_pa'] == 'Y' ? 'Disetujui' : 'Menunggu' }}</td>
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
                                <th style="width: 100px;">Jumlah SKS</th>
                                <th style="width: 10px;">:</th>
                                <th>{{ $value['jumlah_sks'] }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
