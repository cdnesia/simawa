@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">SELAMAT DATANG {{ Auth::user()->name ?? 'NULL' }}</h6>
    <hr>
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center mt-2">
                <div>
                    <h6>Tagihan Pembayaran</h6>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="align-middle" rowspan="2" style="width: 30px">No.</th>
                            <th class="align-middle" rowspan="2" style="width: 120px">Virtual Account</th>
                            <th class="align-middle" rowspan="2" style="width: 120px">Tahun Akademik</th>
                            <th class="align-middle" rowspan="2">Rincian Tagihan</th>
                            <th class="align-middle" rowspan="2" style="width: 120px">Total Tagihan</th>
                            <th class="align-middle text-center" colspan="3">Jumlah</th>
                        </tr>
                        <tr>
                            <th>Ditagihkan</th>
                            <th>Terbayar</th>
                            <th>Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tagihan_sekarang as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['nomor_tagihan'] }}</td>
                                <td>{{ $item['tahun_akademik'] }}</td>
                                <td>
                                    @foreach (json_decode($item['detail_tagihan']) as $val)
                                        <span class="d-block">
                                            {{ $val->nama_bipot }}
                                            Rp. {{ number_format($val->nominal ?? 0, 0, ',', '.') }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    Rp. {{ number_format($item['total_tagihan'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td>Rp. {{ number_format($item['nominal_ditagih'] ?? 0, 0, ',', '.') }}</td>
                                <td>Rp. {{ number_format($item['nominal_terbayar'] ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    Rp.
                                    {{ number_format(($item['total_tagihan'] ?? 0) - ($item['nominal_terbayar'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($tagihan_terhutang as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['nomor_tagihan'] }}</td>
                                <td>{{ $item['tahun_akademik'] }}</td>
                                <td>
                                    @foreach (json_decode($item['detail_tagihan']) as $val)
                                        <span class="d-block">
                                            {{ $val->nama_bipot }}
                                            Rp. {{ number_format($val->nominal ?? 0, 0, ',', '.') }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    Rp. {{ number_format($item['total_tagihan'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td>Rp. {{ number_format($item['nominal_ditagih'] ?? 0, 0, ',', '.') }}</td>
                                <td>Rp. {{ number_format($item['nominal_terbayar'] ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    Rp.
                                    {{ number_format(($item['total_tagihan'] ?? 0) - ($item['nominal_terbayar'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            Tata Cara Pembayaran ke Bank BSI <a href="{{ asset('assets/panduan/tata-cara-pembayaran-bsi.pdf') }}">Klik di sini</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center mt-2">
                        <div>
                            <h6>Pengumuman</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center mt-2">
                        <div>
                            <h6>Grafik IP Semester</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-0">
                        <canvas id="chartIP"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
@endpush
@push('js')
    <script src="{{ asset('') }}assets/plugins/chartjs/js/chart.js"></script>
    <script>
        $(document).ready(function() {
            $('#generateva').click(function() {
                let url = 'generate-va';
                let npm = $(this).data('npm');
                $.ajax({
                    url: url,
                    data: {
                        npm: npm
                    },
                    success: function(res) {
                        window.alert(res.message)
                        location.reload()
                    },
                    error: function(xhr) {

                    }
                });
            });

            var ctx = document.getElementById('chartIP').getContext('2d');

            var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
            gradientStroke1.addColorStop(0, '#00b09b');
            gradientStroke1.addColorStop(1, '#96c93d');

            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'IP Semester',
                        data: @json($ips),
                        backgroundColor: [
                            gradientStroke1
                        ],
                        fill: {
                            target: 'origin',
                            above: 'rgb(21 202 32 / 15%)',
                            below: 'rgb(21 202 32 / 100%)'
                        },
                        tension: 0.4,
                        borderColor: [
                            gradientStroke1
                        ],
                        borderWidth: 3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })

        function salinTeks() {
            const teks = document.getElementById('va').innerText;
            navigator.clipboard.writeText(teks)
                .then(() => alert("Virtual Account disalin: " + teks))
                .catch(err => alert("Gagal menyalin Virtual Account"));
        }
    </script>
@endpush
