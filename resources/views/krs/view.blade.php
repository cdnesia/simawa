@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Kartu rencana studi</h6>
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

            <a href="{{ route('krs.create') }}" class="btn btn-success btn-sm me-1"><i class="bx bx-file"></i>Kontrak Mata
                Kuliah</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <h5>Kartu Rencana Studi</h5>
                @if ($krs['semester'] && $krs['tahun_akademik'])
                    <h5>Semester {{ $krs['semester'] }} - Tahun Akademik {{ $krs['tahun_akademik'] }}</h5>
                    <a href="{{ route('krs.print') }}?periode={{ $krs['tahun_akademik'] }}" target="_blank"
                        class="btn btn-sm btn-primary me-0"><i class="bx bx-printer mr-1"></i> Cetak</a>
                @endif

            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered krsTable" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 30px">No</th>
                            <th style="width: 200px">Hari/Ruang/Jam</th>
                            <th style="width: 400px">Mata Kuliah</th>
                            <th>Dosen Pengampu</th>
                            <th>Persetujuan PA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($krs['krs'] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['nama_hari'] }}/{{ $item['ruang_id'] }}<br>
                                    {{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}</td>
                                <td>{{ $item['kode_mata_kuliah'] }}<br>
                                    {{ $item['nama_mata_kuliah'] }}</td>
                                <td>{{ $item['dosen_id'] }}</td>
                                <td>{{ $item['persetujuan_pa'] == 'Y' ? 'Disetujui' : 'Menunggu' }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <nav aria-label="Page navigation example mb-0">
                Pilih Semester :
                <ul class="pagination mb-0">
                    @foreach ($semester as $key => $item)
                        <li class="page-item {{ request('periode') == $item ? 'active' : '' }}"><a class="page-link"
                                href="{{ route('krs.index') }}?periode={{ $item['tahun_akademik'] }}">{{ $item['semester'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
@endsection
