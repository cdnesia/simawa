@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">jadwal perkuliahan</h6>
    <hr>
    @foreach ($jadwal_kuliah as $key => $value)
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h6 class="mb-0">Semester {{ $value['semester'] ?? '' }}
                </h6>
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
                                <th>Hari/Ruang/Jam</th>
                                <th style="max-width: 270px">Mata Kuliah</th>
                                <th>Dosen Pengampu</th>
                                <th>Kelompok</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($value['krs'] ?? [] as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['hari'] }}/{{ $item['ruang_id'] }}<br>
                                        {{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}</td>
                                    <td>{{ $item['kode_mata_kuliah'] }}<br>
                                        {{ $item['nama_mata_kuliah'] }}</td>
                                    <td>{{ $item['dosen_id'] }}</td>
                                    <td>{{ $item['kelompok'] }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
@push('css')
    <link href="{{ asset('') }}assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
@endpush
@push('js')
    <script src="{{ asset('') }}assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('') }}assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.krsTable').each(function() {
                $(this).DataTable({
                    lengthChange: false,
                    info: false,
                    searching: false,
                    paging: false,
                    scrollX: true,
                    ordering: false
                });
            });
        });
    </script>
@endpush
