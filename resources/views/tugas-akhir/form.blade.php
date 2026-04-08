@extends('layouts.app')
@section('content')
    <h6 class="text-uppercase">Seminar Proposal Tugas Akhir</h6>
    <hr>
    @if (!$jadwal_kkn)
        <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show py-2">
            <div class="d-flex align-items-center">
                <div class="font-35 text-dark"><i class='bx bx-info-circle'></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0 text-dark">Mohon Maaf !!!</h6>
                    <div class="text-dark">
                        Jadwal kontrak pendaftaran KKN belum dibuka/sudah berakhir.
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-1 row-cols-lg-3 row-cols-xl-3">
            @foreach ($persyaratan as $item)
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item['nama_kegiatan'] }}</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Persyaratan</strong></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Minimal SKS
                                {{ $item['minimal_sks'] }}
                                @if ($jumlah_sks >= $item['minimal_sks'])
                                    <span class="badge bg-success rounded-pill">
                                        Memenuhi
                                    </span>
                                @else
                                    <span class="badge bg-danger rounded-pill">
                                        Tidak Memenuhi
                                    </span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Minimal Semester
                                {{ $item['minimal_semester'] }}
                                <span class="badge bg-success rounded-pill">
                                    Memenuhi
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Maksimal Nilai D
                                {{ $item['maksimal_nilai_d'] }}
                                @if ($jumlah_d <= $item['maksimal_nilai_d'])
                                    <span class="badge bg-success rounded-pill">
                                        Memenuhi
                                    </span>
                                @else
                                    <span class="badge bg-danger rounded-pill">
                                        Tidak Memenuhi
                                    </span>
                                @endif
                            </li>
                        </ul>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-primary btn-sm btn-daftar"
                                data-id="{{ $item['encrypted_id'] }}">
                                Daftar
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
@push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".btn-daftar").forEach(function(button) {
                button.addEventListener("click", function() {
                    let encryptedId = this.getAttribute("data-id");
                    fetch("{{ route('pendaftaran-seminar-proposal.store') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                id: encryptedId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                window.location.href = "{{ route('pendaftaran-seminar-proposal.index') }}";
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            alert("Terjadi kesalahan");
                        });

                });

            });

        });
    </script>
@endpush
