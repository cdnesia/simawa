@extends("layouts.app")

@section("content")
    <h6 class="mb-0 text-uppercase">Kontrak Mata Kuliah</h6>
    <hr />
    <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-dark"><i class='bx bx-info-circle'></i>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-dark">Mohon Maaf !!!</h6>
                <div class="text-dark">
                    Jadwal kontrak mata kuliah belum dibuka/sudah berakhir, anda tidak dapat melakukan kontrak mata kuliah.
                </div>
            </div>
        </div>
    </div>
@endsection
@push("css")
@endpush
@push("js")
@endpush
