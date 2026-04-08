@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Seminar Proposal Tugas Akhir</h6>
    <hr>
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <div class="ms-auto">
                <a href="{{ route($modul . '.create') }}" class="btn btn-sm btn-primary">Daftar</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered example" style="width:100%">
                    <thead>
                        <tr>
                            <th width="30px">No</th>
                            <th>Nomor Tagihan</th>
                            <th>Keterangan</th>
                            <th>Tanggal Pendaftaran</th>
                            <th>Biaya Pendaftaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($seminar as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('home') }}">Lihat Virtual Account</a></td>
                                <td>{{ $item->kegiatan_mahasiswa }}</td>
                                <td>{{ $item->tanggal_pendaftaran }}</td>
                                <td>Rp. {{ number_format($item->biaya_pendaftaran ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <link href="{{ asset('') }}assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
@endpush
@push('js')
    <script src="{{ asset('') }}assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('') }}assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.example').each(function() {
                $(this).DataTable({
                    lengthChange: false,
                    info: false,
                    paging: false,
                    scrollX: true,
                });
            });
        });
    </script>
@endpush
