<?php

namespace App\Http\Controllers;

use App\Models\KegiatanMahasiswa;
use App\Models\Krs;
use App\Models\PendaftaranKKN;
use App\Services\DataService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PendaftaranKKNController extends Controller
{
    private $modul = 'pendaftaran-kkn';
    public function __construct()
    {

        view()->share('modul', $this->modul);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(DataService $dataService)
    {
        $npm = auth('web')->user()->npm;
        $d['kkn'] = DB::connection('db_siade')->table('tbl_pendaftaran_kegiatan_mahasiswa  as tpkm')
            ->join('tbl_kegiatan_mahasiswa as tkm', 'tpkm.kegiatan_mahasiswa_id', 'tkm.id')
            ->where('npm', $npm)
            ->whereIn('tkm.tipe', ['KKN', 'PKL'])
            ->select('tpkm.*', 'tkm.nama_kegiatan')
            ->get();
        return view('kkn.view', $d);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DataService $dataService)
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahunAngkatan = auth('web')->user()->mahasiswa->tahun_angkatan;
        $kelasPerkuliahan = auth('web')->user()->mahasiswa->program_kuliah_id;

        $cekJadwalKKN = $dataService->JadwalKKN();
        if (!$cekJadwalKKN) {
            $d['jadwal_kkn'] = false;
        }
        $d['jadwal_kkn'] = true;
        $d['data'] = null;

        $d['persyaratan'] = KegiatanMahasiswa::where('tipe', 'KKN')
            ->where('kelas_perkuliahan_id', $kelasPerkuliahan)
            ->whereJsonContains('kode_program_studi', $kodeProdi)
            ->whereJsonContains('tahun_angkatan', $tahunAngkatan)
            ->get()
            ->map(function ($item) {
                $item->encrypted_id = encrypt($item->id);
                unset($item->id);
                return $item;
            })->toArray();

        $tahunAktif = $dataService->tahunAkademikAktif($kodeProdi);
        $krs = $dataService->Krs(auth('web')->user()->mahasiswa->npm);


        $flatKrs = collect($krs)
            ->reject(function ($semester, $tahun) use ($tahunAktif) {
                return $tahun == $tahunAktif;
            })
            ->pluck('krs')
            ->flatten(1);

        $total_sks = $flatKrs->sum('sks_matakuliah');
        $jumlahD = $flatKrs->where('nilai_huruf', 'D')->count();
        $jumlahKosong = $flatKrs->where('nilai_huruf', '')->count();

        $d['jumlah_sks'] = $total_sks;
        $d['jumlah_d'] = $jumlahD + $jumlahKosong;
        return view('kkn.form', $d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DataService $dataService, PaymentService $paymentService)
    {
        try {
            $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
            $npm = auth('web')->user()->mahasiswa->npm;

            $tahunAktif = $dataService->tahunAkademikAktif($kodeProdi);
            $krs = collect($dataService->Krs($npm));

            $krsRaw = $krs->pluck('krs')->flatten(1);

            $dataSaya = $dataService->saya($npm);
            $id_fakultas = $dataSaya['id_fakultas'] ?? null;

            $excludeFakultas = [2];

            if (
                !in_array($id_fakultas, $excludeFakultas) &&
                $krsRaw->where('tipe_mata_kuliah', 1)->isEmpty()
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendaftar karena belum kontrak Matakuliah KKN.'
                ], 422);
            }

            $flatKrs = $krs
                ->reject(fn($item, $tahun) => $tahun == $tahunAktif)
                ->pluck('krs')
                ->flatten(1);

            $total_sks = $flatKrs->sum('sks_matakuliah');
            $jumlahD = $flatKrs->where('nilai_huruf', 'D')->count();
            $jumlahKosong = $flatKrs->filter(fn($item) => empty($item['nilai_huruf']))->count();
            $id = Crypt::decrypt($request->id);

            $persyaratan = KegiatanMahasiswa::findOrFail($id);

            if ($total_sks < $persyaratan->minimal_sks) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendaftar karena tidak memenuhi persyaratan SKS.'
                ], 422);
            }
            if (($jumlahD + $jumlahKosong) > $persyaratan->maksimal_nilai_d) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendaftar karena tidak memenuhi persyaratan nilai D.'
                ], 422);
            }

            $generate = $paymentService->generateTagihanKKN($id);

            if (!$generate['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $generate['message'] ?? 'Gagal membuat tagihan'
                ], 400);
            }


            PendaftaranKkn::insert([
                'npm' => auth('web')->user()->mahasiswa->npm,
                'kegiatan_mahasiswa_id'  => $persyaratan->id,
                'tanggal_pendaftaran' => now(),
                'id_bipot' => $persyaratan->id_bipot,
                'biaya_pendaftaran' => $persyaratan->biaya_pendaftaran,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar KKN'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PendaftaranKKN $pendaftaranKKN)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PendaftaranKKN $pendaftaranKKN)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PendaftaranKKN $pendaftaranKKN)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PendaftaranKKN $pendaftaranKKN)
    {
        //
    }
}
