@extends("layouts.app")

@section("content")
    <h6 class="mb-0 text-uppercase">Kartu Hasil Studi (KHS)</h6>
    <hr />
    <div class="card">
        <div class="card-header">
            <button id="cetak" class="btn btn-primary btn-sm px-3"><i class="bx bx-printer"></i>Cetak</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>KODE MATA KULIAH</th>
                            <th>MATA KULIAH</th>
                            <th>SKS</th>
                            <th>NILAI ANGKA</th>
                            <th>NILAI BOBOT</th>
                            <th>NILAI HURUF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                            $ipk = 0;
                            $total_sks = 0;
                        @endphp
                        @foreach ($transkrip as $a => $item)
                            @php
                                $ipk = (float) $ipk + (float) $item["ips"];
                                $total_sks = $total_sks + $item["total_sks"];
                            @endphp
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
                    </tbody>
                </table>

                <div id="khs-meta" class="mt-3 d-flex align-items-center gap-4 flex-wrap">
                    <div><strong>IP KUMULAITF : <span id="meta-ips">{{ number_format($ipk / count($transkrip), 2) }}</span>
                    </div></strong>
                    <div><strong>TOTAL SKS : <span id="meta-sks">{{ $total_sks }}</span></div></strong>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("css")
    <link href="{{ asset("") }}assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="{{ asset("") }}assets/plugins/notifications/css/lobibox.min.css" />
@endpush
@push("js")
    <script src="{{ asset("") }}assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset("") }}assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#example').DataTable({
                bLengthChange: false,
                paging: false,
                searching: false,
                ordering: false,
                info: false,
            });
        });

        $('#cetak').on('click', function() {
            window.open(`/cetak-transkrip`, '_blank');
        });
    </script>

    <script src="{{ asset("") }}assets/plugins/notifications/js/lobibox.min.js"></script>
    <script src="{{ asset("") }}assets/plugins/notifications/js/notifications.min.js"></script>
    <script src="{{ asset("") }}assets/plugins/notifications/js/notification-custom-script.js"></script>
@endpush
