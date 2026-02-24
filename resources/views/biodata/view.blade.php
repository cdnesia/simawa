@extends('layouts.app')
@section('content')
    <h6 class="mb-0 text-uppercase">Biodata Mahasiswa</h6>
    <hr />
    <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-dark"><i class='bx bx-info-circle'></i>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-dark">Peringatan !!!</h6>
                <div class="text-dark">Anda belum melakukan pembaruan data, silahkan perbarui terlebih dahulu melalui menu
                    <strong>Biodata</strong> atau <a href="{{ route('biodata.index') }}">Klik disini</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center mt-2">
                <div>
                    <h6>Biodata Mahasiswa</h6>
                </div>
            </div>
        </div>
        <div class="card-body">
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
        let table = $('#example').DataTable({
            bLengthChange: false,
            paging: false,
            searching: false,
            ordering: false,
            info: false,
        });
    </script>
@endpush
