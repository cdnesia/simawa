<?php

namespace App\Http\Controllers;

use App\Models\KegiatanMahasiswa;
use App\Models\TugasAkhir;
use App\Services\DataService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PendaftaranSidangController extends Controller
{
    private $modul = 'pendaftaran-sidang-tugas-akhir';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $npm = auth('web')->user()->npm;
        $d['sidang'] = DB::connection('db_siade')->table('tbl_tugas_akhir as tpkm')
            ->where('npm', $npm)
            ->where('kegiatan_mahasiswa', 'SIDANG TUGAS AKHIR')
            ->get();
        return view('pendaftaran-sidang.view', $d);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DataService $dataService)
    {
        $npm = auth('web')->user()->mahasiswa->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahunAngkatan = auth('web')->user()->mahasiswa->tahun_angkatan;
        $kelasPerkuliahan = auth('web')->user()->mahasiswa->program_kuliah_id;

        $cekJadwal = $dataService->jadwalSidang();
        if (!$cekJadwal) {
            $d['jadwal_kkn'] = false;
        }

        $d['jadwal_kkn'] = true;
        $d['data'] = null;
        $d['persyaratan'] = KegiatanMahasiswa::where('tipe', 'SIDANG TUGAS AKHIR')
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
        $krs = $dataService->Krs($npm);
        $krsRaw = collect($krs)->pluck('krs')->flatten(1);

        $d['sudah_kontrak_skripsi'] = $krsRaw->where('tipe_mata_kuliah', 3)->isNotEmpty();

        $flatKrs = collect($krs)
            ->reject(function ($item, $tahun) use ($tahunAktif) {
                return $tahun == $tahunAktif;
            })
            ->pluck('krs')
            ->flatten(1);

        $tipeDikecualikanNilaiD = [1, 3, 4]; // KKN, Skripsi, Seminar Proposal
        $flatKrsUntukNilaiD = $flatKrs->reject(fn($item) => in_array($item['tipe_mata_kuliah'], $tipeDikecualikanNilaiD));

        $total_sks = $flatKrs->sum('sks_matakuliah');
        $jumlahD = $flatKrsUntukNilaiD->where('nilai_huruf', 'D')->count();
        $jumlahKosong = $flatKrsUntukNilaiD->where('nilai_huruf', '')->count();

        $d['jumlah_sks'] = $total_sks;
        $d['jumlah_d'] = $jumlahD + $jumlahKosong;
        return view('pendaftaran-sidang.form', $d);
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

            if ($krsRaw->where('tipe_mata_kuliah', 3)->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum kontrak Skripsi.'
                ], 422);
            }

            $flatKrs = $krs
                ->reject(fn($item, $tahun) => $tahun == $tahunAktif)
                ->pluck('krs')
                ->flatten(1);

            $tipeDikecualikanNilaiD = [1, 3, 4]; // KKN, Skripsi, Seminar Proposal
            $flatKrsUntukNilaiD = $flatKrs->reject(fn($item) => in_array($item['tipe_mata_kuliah'], $tipeDikecualikanNilaiD));

            $total_sks = $flatKrs->sum('sks_matakuliah');
            $jumlahD = $flatKrsUntukNilaiD->where('nilai_huruf', 'D')->count();
            $jumlahKosong = $flatKrsUntukNilaiD->filter(fn($item) => empty($item['nilai_huruf']))->count();
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

            $generate = $paymentService->generateTagihanSidangAkhir($id);

            if ($generate['error_code'] !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => $generate['error_desc'] ?? 'Gagal membuat tagihan'
                ], 400);
            }

            TugasAkhir::insert([
                'npm' => auth('web')->user()->mahasiswa->npm,
                'kegiatan_mahasiswa'  => 'SIDANG TUGAS AKHIR',
                'tanggal_pendaftaran' => now(),
                'id_bipot' => $persyaratan->id_bipot,
                'biaya_pendaftaran' => $persyaratan->biaya_pendaftaran,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar Sidang Akhir'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
