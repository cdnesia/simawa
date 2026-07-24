@extends('layouts.app')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <h6 class="text-uppercase">Evaluasi Dosen</h6>
    <hr>
    <div class="card">
        <div class="card-header">
            <div class="table-responsive">
                <div class="card-body">

                    <div class="row">
                        <!-- User Sidebar -->
                        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                            <!-- User Card -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="user-avatar-section">
                                        <div class="d-flex align-items-center flex-column">
                                            <?php //if(!empty($dosen->foto) && !empty($dosen->foto)){
                                            ?>
                                            {{-- <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                                src="https://portal.umjambi.ac.id/assets/uploads/foto/{{ $matakuliah->foto }}"
                                                height="100" width="100" alt="User avatar" /> --}}
                                            <?php //}else{
                                            ?>
                                            <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                                src="{{ asset('assets/images/no-image.png') }}" height="100"
                                                width="100" alt="User avatar" />
                                            <?php //}
                                            ?>
                                            <div class="user-info text-center">
                                                <h4 class="mb-2">{{ $dosen->nidn ?? '' }}</h4>
                                                <h4 class="mb-2">{{ $dosen->nama_lengkap ?? '' }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /User Card -->
                        </div>
                        <!--/ User Sidebar -->

                        <!-- User Content -->
                        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
                            <!-- Project table -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h3 class="mt-4 text-uppercase text-muted">Info Detail</h3>
                                    <div class="info-container">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <span class="fw-semibold me-1">Tahun Akademik:</span>
                                                <span>{{ substr($matakuliah->kode_tahun_akademik, 0, 4) }}
                                                    {{ substr($matakuliah->kode_tahun_akademik, -1) % 2 == 1 ? 'Ganjil' : 'Genap' }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <span class="fw-semibold me-1">kode Matakuliah:</span>
                                                <span>{{ $matakuliah->kode_mata_kuliah }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <span class="fw-semibold me-1">Matakuliah:</span>
                                                <span>{{ $matakuliah->nama_mata_kuliah_idn }}</span>
                                            </li>
                                            <li class="mb-2 pt-1">
                                                <a href="{{ url('khs') }}?periode={{ $matakuliah->kode_tahun_akademik }}"
                                                    class="btn btn-warning">
                                                    <i class="fa-solid fa-angles-left me-1"></i> Kembali
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ User Content -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <h5>EVALUASI DOSEN OLEH MAHASISWA (EDOM) Universitas Muhammadiyah Jambi</h5>
                <p class="card-text">
                    Assalammu'alaiukum Warahmatullahi Wabarakatuh,
                    <br>
                    Salam Hormat,,
                    <br>
                    Saudara mahasiswa yang kami hormati. Survey ini bertujuan untuk mengetahui Evaluasi Dosen oleh
                    Mahasiswa di Universitas Muhammadiyah Jambi dengan tujuan sebagai masukan dalam upaya untuk
                    meningkatkan kualitas penyelenggaraan proses pendidikan dan melaksanakan proses perubahan yang
                    lebih baik. Kami menjamin kerahasiaan identitas saudara mahasiswa sekalian.
                    <br>
                    Demikian disampaikan, atas perhatian dan partisipasi Saudara, kami mengucapkan terima kasih.
                </p>
                <hr>
                <div class="card-body">
                    <form id="formEdome" class="form-horizontal" autocomplete="off" action="{{ url('simpan_edome') }}"
                        method="post">

                        @csrf
                        <input type="hidden" name="nim" value="<?php echo $matakuliah->npm; ?>">
                        <input type="hidden" name="id_mhsw_krs" value="<?php echo $matakuliah->npm; ?>">
                        <input type="hidden" name="tahunid" value="<?php echo $matakuliah->kode_tahun_akademik; ?>">
                        <input type="hidden" name="idmk" value="<?php echo $matakuliah->id_matakuliah; ?>">
                        <input type="hidden" name="dosenid" value="<?php echo $id_dosen; ?>">
                        {{-- soal --}}
                        <?php $no = 1; ?>
                        @foreach ($daftar_soal as $rs_soal)
                            <h5 class="card-header"><?= $no ?>. {{ $rs_soal['pertanyaan'] }}</h5>
                            <input type="hidden" name="id_listsoal[<?php echo $no; ?>]" value="<?php echo $rs_soal['id_listsoal']; ?>">
                            <input type="hidden" name="tipe_soal[<?php echo $no; ?>]" value="<?php echo $rs_soal['tipesoal']; ?>">
                            <?php
                         $tipesoal = $rs_soal['tipesoal'];
                         if ($tipesoal == 'PG'){
                         ?>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    @foreach ($rs_soal['pilihan'] as $rs_pilihan)
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-success">
                                                    <input name="jawaban[<?php echo $no; ?>]" class="form-check-input"
                                                        type="radio" value="<?= $rs_pilihan['urut'] ?>"
                                                        id="customRadioSuccess{{ $rs_pilihan['id_listsoal'].$rs_pilihan['urut'] }}" required />
                                                    <label class="form-check-label"
                                                        for="customRadioSuccess{{ $rs_pilihan['id_listsoal'].$rs_pilihan['urut'] }}">
                                                        {{ $rs_pilihan['ket'] }}
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                            <?php }else{?>
                            <div>
                                <label for="exampleFormControlTextarea1" class="form-label">Isi Jawaban Anda</label>
                                <textarea name="jawaban_esay[<?php echo $no; ?>]" class="form-control" id="exampleFormControlTextarea1" rows="3"
                                    required></textarea>
                            </div>
                            <?php }?>
                            <?php $no++; ?>
                        @endforeach
                        <hr>
                        <input type="submit" name="add" class="btn btn-success btn-custom" value="Simpan">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {

            $('#formEdome').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ url('simpan_edome') }}",
                    type: "POST",
                    data: $(this).serialize(),

                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ url('khs') }}?periode={{ $matakuliah->kode_tahun_akademik }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message
                            });
                        }
                    },

                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan server'
                        });
                    }
                });
            });

        });
    </script>
@endsection
