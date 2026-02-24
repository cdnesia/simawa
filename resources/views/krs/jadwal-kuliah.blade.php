@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Kontrak Mata Kuliah</h6>
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

            <a href="{{ route('krs.index') }}" class="btn btn-success btn-sm"><i class="bx bx-file-find mr-1"></i>Lihat KRS</a>
        </div>
    </div>
    @foreach ($jadwal_perkuliahan as $key => $value)
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center mt-2">
                    <div>
                        <h6>Semester {{ $key }}</h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered krsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px">No</th>
                                <th style="max-width: 350px">Mata Kuliah</th>
                                <th>Hari/Ruang/Jam</th>
                                <th>Dosen Pengampu</th>
                                <th>Kelompok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($value['jadwal_kuliah'] as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['kode_mata_kuliah'] }}<br>
                                        {{ $item['nama_mata_kuliah'] }}</td>
                                    <td>{{ $item['nama_hari'] }}/{{ $item['ruang_id'] }}<br>
                                        {{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}</td>
                                    <td>{{ $item['dosen_id'] }}</td>
                                    <td>{{ $item['kelompok'] }}</td>
                                    <td style="width: 30px">
                                        @php
                                            try {
                                                $decryptedId = \Illuminate\Support\Facades\Crypt::decrypt(
                                                    $item['jadwal_id'],
                                                );
                                            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                                $decryptedId = null;
                                            }
                                        @endphp

                                        @if (in_array($decryptedId, $existing))
                                            <button data-id="{{ $item['jadwal_id'] }}"
                                                class="btn btn-danger btn-sm btn-batal">
                                                <i class="bx bx-x-circle me-0"></i>
                                            </button>
                                        @else
                                            <button data-id="{{ $item['jadwal_id'] }}"
                                                class="btn btn-success btn-sm btn-pilih">
                                                <i class="bx bx-plus-circle me-0"></i>
                                            </button>
                                        @endif
                                    </td>
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
                    ordering: false
                });
            });

            $(document).on('click', '.btn-hapus', function() {
                let id = $(this).data('id');
                let button = $(this);

                if (!confirm('Yakin ingin menghapus data ini?')) {
                    return;
                }

                $.ajax({
                    url: '/krs/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            button.closest('tr').remove();
                        } else {
                            alert('Gagal menghapus data');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message
                            alert(msg);

                        }
                    }
                });

            });

            $(document).on('click', '.btn-pilih', function() {

                let id = $(this).data('id');
                let button = $(this);

                $.ajax({
                    url: '/krs',
                    type: 'POST',
                    data: {
                        jadwal_id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Lobibox.notify('success', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-check-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });
                            button
                                .removeClass('btn-success btn-pilih')
                                .addClass('btn-danger btn-batal')
                                .html('<i class="bx bx-x-circle me-0"></i>');

                        } else {
                            Lobibox.notify('error', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-x-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message
                            alert(msg);

                        }
                    }
                });
            });

            $(document).on('click', '.btn-batal', function() {

                let id = $(this).data('id');
                let button = $(this);

                if (!confirm('Yakin ingin membatalkan mata kuliah ini?')) {
                    return;
                }

                $.ajax({
                    url: '/krs/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Lobibox.notify('success', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-check-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });
                            button
                                .removeClass('btn-danger btn-batal')
                                .addClass('btn-success btn-pilih')
                                .html('<i class="bx bx-plus-circle me-0"></i>');

                        } else {
                            Lobibox.notify('error', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-x-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message
                            alert(msg);
                        }
                    }
                });
            });

        });
    </script>
@endpush
