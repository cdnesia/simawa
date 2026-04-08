<?php

namespace App\Http\Controllers;

use App\Models\KegiatanMahasiswa;
use App\Models\PendaftaranSeminar;
use App\Models\TugasAkhir;
use App\Services\DataService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PendaftaranSeminarController extends Controller
{
    private $modul = 'pendaftaran-seminar-proposal';
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
        $d['seminar'] = DB::connection('db_siade')->table('tbl_tugas_akhir  as tpkm')
            ->where('npm', $npm)
            ->get();
        return view('tugas-akhir.view', $d);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DataService $dataService)
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahunAngkatan = auth('web')->user()->mahasiswa->tahun_angkatan;
        $kelasPerkuliahan = auth('web')->user()->mahasiswa->program_kuliah_id;

        $cekJadwal = $dataService->jadwalSempro();
        if (!$cekJadwal) {
            $d['jadwal_kkn'] = false;
        }

        $d['jadwal_kkn'] = true;
        $d['data'] = null;
        $d['persyaratan'] = KegiatanMahasiswa::where('tipe', 'SEMINAR PROPOSAL')
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
            ->reject(function ($tahun) use ($tahunAktif) {
                return $tahun == $tahunAktif;
            })
            ->pluck('krs')
            ->flatten(1);

        $total_sks = $flatKrs->sum('sks_matakuliah');
        $jumlahD = $flatKrs->where('nilai_huruf', 'D')->count();
        $jumlahKosong = $flatKrs->where('nilai_huruf', '')->count();

        $d['jumlah_sks'] = $total_sks;
        $d['jumlah_d'] = $jumlahD + $jumlahKosong;
        return view('tugas-akhir.form', $d);
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

            if (!in_array($id_fakultas, $excludeFakultas)) {
                if ($krsRaw->where('tipe_mata_kuliah', 1)->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum kontrak KKN.'
                    ], 422);
                }

                if ($krsRaw->where('tipe_mata_kuliah', 4)->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum kontrak Seminar Proposal.'
                    ], 422);
                }
            } else {
                if ($krsRaw->where('tipe_mata_kuliah', 2)->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum kontrak PKL.'
                    ], 422);
                }
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

            $url = config('services.simaku_url');
            $npm = auth('web')->user()->npm;
            $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
            $tahun_akademik = $dataService->tahunAkademikAktif($kodeProdi);

            $timestamp = time();
            $nonce = Str::uuid()->toString();
            $path = 'api/generate-tagihan-seminar-proposal';

            $body = json_encode([
                'npm' => $npm,
                'tahun_akademik' => $tahun_akademik,
                'kegiatan_mahasiswa_id' => $id,
            ]);

            $data = $timestamp . $nonce . 'POST' . $path . $body;
            $signature = hash_hmac('sha256', $data, config('services.hmac_secret'));
            $response = Http::withHeaders([
                'X-API-KEY'   => config('services.hmac_api_key'),
                'X-TIMESTAMP' => $timestamp,
                'X-NONCE'     => $nonce,
                'X-SIGNATURE' => $signature,
            ])->withBody($body, 'application/json')
                ->post($url . $path);

            $responseData = $response->json();

            if (!$responseData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat tagihan'
                ], 400);
            }

            TugasAkhir::insert([
                'npm' => auth('web')->user()->mahasiswa->npm,
                'kegiatan_mahasiswa'  => 'SEMINAR PROPOSAL',
                'tanggal_pendaftaran' => now(),
                'id_bipot' => $persyaratan->id_bipot,
                'biaya_pendaftaran' => $persyaratan->biaya_pendaftaran,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar Seminar Proposal'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
