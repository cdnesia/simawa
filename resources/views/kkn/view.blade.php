@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Pendaftaran Kuliah Kerja Nyata (KKN)</h6>
    <hr>
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <div class="ms-auto">
                <a href="{{ route($modul . '.create') }}" class="btn btn-sm btn-primary">Pendaftaran</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered example" style="width:100%">
                    <thead>
                        <tr>
                            <th width="30px">No</th>
                            <th>Jenis KKN</th>
                            <th>Tanggal Pendaftaran</th>
                            <th>Status</th>
                            <th width="50px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
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
