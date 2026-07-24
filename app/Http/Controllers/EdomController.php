<?php

namespace App\Http\Controllers;

use App\Models\Khs;
use App\Services\DataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class EdomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $npm = auth('web')->user()->npm;
        $npm2 = $request->npm;
        $periode = $request->periode;
        $id_dosen = $request->id_dosen;
        $id_matakuliah = $request->id_matakuliah;

        if (
            empty($npm2) ||
            empty($periode) ||
            empty($id_dosen) ||
            empty($id_matakuliah)
        ) {
            abort(404);
        }

        $d['matakuliah'] = DB::connection('db_siade')
            ->table('tbl_mahasiswa_krs')
            ->join('tbl_jadwal_perkuliahan', 'tbl_jadwal_perkuliahan.id', '=', 'tbl_mahasiswa_krs.jadwal_id')
            ->join('master_kurikulum_matakuliah', 'master_kurikulum_matakuliah.id', '=', 'tbl_jadwal_perkuliahan.mata_kuliah_id')
            ->select(
                'tbl_mahasiswa_krs.kode_tahun_akademik',
                'tbl_mahasiswa_krs.npm',
                'master_kurikulum_matakuliah.id as id_matakuliah',
                'master_kurikulum_matakuliah.kode_mata_kuliah',
                'master_kurikulum_matakuliah.nama_mata_kuliah_idn',
                'tbl_jadwal_perkuliahan.dosen_id'
            )
            ->where('tbl_mahasiswa_krs.npm', $npm)
            ->where('tbl_mahasiswa_krs.kode_tahun_akademik', $periode)
            ->where('master_kurikulum_matakuliah.id', $id_matakuliah)
            ->where('tbl_jadwal_perkuliahan.dosen_id', $id_dosen)
            ->first();

        if (!$d['matakuliah']) {
            abort(404);
        }

        $id_dosen =  $d['matakuliah']->dosen_id;

        $d['dosen'] = DB::connection('db_siade_old')
            ->table('pegawai')
            ->where('id', $id_dosen)
            ->first();

        //daftar soal edom
        $soal = DB::connection('db_siade')
            ->table('tbl_listsoal_survey')
            ->orderBy('id_listsoal', 'asc')
            ->get();
        $daftar_soal = [];
        foreach ($soal as $rs_soal) {
            $id_listsoal = $rs_soal->id_listsoal;
            $details = DB::connection('db_siade')
                ->table('tbl_listpilihan_survey')
                ->select(
                    'tbl_listpilihan_survey.*',
                )
                ->join('tbl_listsoal_survey', 'tbl_listpilihan_survey.id_listsoal', '=', 'tbl_listsoal_survey.id_listsoal')
                ->where('tbl_listpilihan_survey.id_listsoal', $id_listsoal)
                ->orderBy('tbl_listpilihan_survey.urut', 'asc')
                ->get();
            $data_detail = [];
            foreach ($details as $rs_pilihan) {
                $data_detail[] = [
                    "id_listsoal" => $rs_pilihan->id_listsoal,
                    "urut" => $rs_pilihan->urut,
                    "ket" => $rs_pilihan->ket
                ];
            }
            $daftar_soal[] = [
                "id_listsoal" => $rs_soal->id_listsoal,
                "tipesoal" => $rs_soal->tipe_soal,
                "pertanyaan" => $rs_soal->pertanyaan,
                "pilihan" => $data_detail,
            ];
        }
        $d['daftar_soal'] = $daftar_soal;
        $d['id_dosen'] = $id_dosen;

        // print_r($d['matakuliah']);
        // exit();
        return view('edom.view', $d);
    }

    public function simpan_edome(Request $request)
    {
        Log::info('Mulai simpan EDOM', $request->all());

        try {
            $id_mhsw_krs = $request->input('id_mhsw_krs');
            $nim         = $request->input('nim');
            $tahun       = $request->input('tahunid');
            $idmk        = $request->input('idmk');
            $dosenid     = $request->input('dosenid');
            $waktuSelesai = Carbon::now('Asia/Jakarta');

            $jmldata = DB::connection('db_siade')
                ->table('tbl_listsoal_survey')
                ->count();

            $data = [];

            for ($i = 1; $i <= $jmldata; $i++) {
                if ($request->input("tipe_soal.$i") === "PG") {
                    $data[] = [
                        'id_listsoal'  => $request->input("id_listsoal.$i"),
                        'idmk'          => $idmk,
                        'nim'          => $nim,
                        'dosenid'      => $dosenid,
                        'tahun'        => $tahun,
                        'id_mhsw_krs'  => $id_mhsw_krs,
                        'jawaban'      => $request->input("jawaban.$i"),
                        'jawaban_esay' => '',
                    ];
                } else {
                    $data[] = [
                        'id_listsoal'  => $request->input("id_listsoal.$i"),
                        'idmk'          => $idmk,
                        'nim'          => $nim,
                        'dosenid'      => $dosenid,
                        'tahun'        => $tahun,
                        'id_mhsw_krs'  => $id_mhsw_krs,
                        'jawaban'      => '',
                        'jawaban_esay' => $request->input("jawaban_esay.$i"),
                    ];
                }
            }

            // print_r($data);
            // exit();

            $dataHeader = [
                'nim'            => $nim,
                'tahunid'        => $tahun,
                'idmk'           => $idmk,
                'dosenid'        => $dosenid,
                'waktu_selesai'  => $waktuSelesai,
            ];

            $simpan = DB::connection('db_siade')
                ->table('tbl_jawaban_detail_survey')
                ->insert($data);

            DB::connection('db_siade')
                ->table('tbl_jawaban_header_survey')
                ->insert($dataHeader);

            Log::info('Simpan EDOM berhasil', [
                'nim' => $nim,
                'id_mhsw_krs' => $id_mhsw_krs
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Jawaban EDOM berhasil disimpan'
            ], 200);
        } catch (\Exception $e) {

            Log::error('Gagal simpan EDOM', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menyimpan jawaban...'
            ], 500);
        }
    }
}
