@extends("layouts.app")

@section("content")
    <h6 class="mb-0 text-uppercase">Kontrak Mata Kuliah</h6>
    <hr />
    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bx-info-circle'></i>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">Mohon Maaf !!!</h6>
                <div class="text-white">
                    Anda belum melakukan pembayara pada semester ini.
                </div>
            </div>
        </div>
    </div>
@endsection
@push("css")
@endpush
@push("js")
@endpush
