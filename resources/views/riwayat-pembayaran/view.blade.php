@extends('layouts.app')

@section('content')
    <h6 class="text-uppercase">Riwayat Pembayaran</h6>
    <hr>

    @forelse ($riwayat_pembayaran as $tahun)
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center mt-2">
                    <div>
                        <h6>Tahun Akademik {{ $tahun['tahun_akademik'] }}</h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 30px">No</th>
                                <th>Rincian Pembayaran</th>
                                <th style="width: 120px" class="text-end">Jumlah</th>
                                <th style="width: 120px" class="text-end">Dibayar</th>
                                <th style="width: 100px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tahun['detail'] as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail['nama_bipot'] }}</td>
                                    <td class="text-end">
                                        Rp. {{ number_format($detail['nominal'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        Rp. {{ number_format($detail['dibayar'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if ($detail['dibayar'] >= $detail['nominal'])
                                            <span class="badge bg-success w-100">Lunas</span>
                                        @else
                                            <span class="badge bg-danger w-100">Belum Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Tidak ada rincian tagihan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            Tidak ada riwayat pembayaran
        </div>
    @endforelse
@endsection
